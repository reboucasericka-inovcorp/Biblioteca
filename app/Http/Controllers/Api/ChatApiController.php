<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteRoomUserRequest;
use App\Http\Requests\MarkMessagesReadRequest;
use App\Http\Requests\StoreChatRoomRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateChatRoomRequest;
use App\Models\ChatConversationRead;
use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;

class ChatApiController extends Controller
{
    private ?bool $conversationReadsTableAvailable = null;

    public function users(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $users = User::query()
            ->select(['id', 'name', 'email', 'avatar', 'status', 'last_seen_at'])
            ->where('id', '!=', $authUser->id);

        if ($search = $request->get('search')) {
            $users->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $users->orderBy('name')->get();
        if ($users->isEmpty()) {
            return ApiResponse::success($users);
        }

        $peerIds = $users->pluck('id')->all();
        $conversations = DirectConversation::query()
            ->where(function ($query) use ($authUser, $peerIds) {
                $query->where('user_one_id', $authUser->id)
                    ->whereIn('user_two_id', $peerIds);
            })
            ->orWhere(function ($query) use ($authUser, $peerIds) {
                $query->where('user_two_id', $authUser->id)
                    ->whereIn('user_one_id', $peerIds);
            })
            ->get();

        $conversationByPeer = [];
        foreach ($conversations as $conversation) {
            $peerId = $conversation->user_one_id === $authUser->id
                ? (int) $conversation->user_two_id
                : (int) $conversation->user_one_id;
            $conversationByPeer[$peerId] = (int) $conversation->id;
        }

        $conversationIds = array_values($conversationByPeer);
        [$lastByTarget, $unreadByTarget] = $this->buildMessageSummaryForTargets(
            DirectConversation::class,
            $conversationIds,
            $authUser->id
        );

        $data = $users->map(function (User $user) use ($conversationByPeer, $lastByTarget, $unreadByTarget) {
            $conversationId = (int) ($conversationByPeer[(int) $user->id] ?? 0);
            $lastMessage = $conversationId > 0 ? ($lastByTarget[$conversationId] ?? null) : null;
            $unreadCount = $conversationId > 0 ? (int) ($unreadByTarget[$conversationId] ?? 0) : 0;

            return [
                'id' => (int) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'status' => $this->isOnline($user->last_seen_at) ? 'online' : 'offline',
                'last_seen_at' => optional($user->last_seen_at)?->toISOString(),
                'direct_conversation_id' => $conversationId > 0 ? $conversationId : null,
                'last_message' => $lastMessage ? $this->messagePreview($lastMessage) : null,
                'unread_count' => $unreadCount,
            ];
        });

        return ApiResponse::success($data);
    }

    public function rooms(Request $request): JsonResponse
    {
        $user = $request->user();

        $rooms = ChatRoom::query()
            ->with(['creator:id,name,email,avatar,status', 'users:id,name,email,avatar,status'])
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->orderBy('name')
            ->get();

        if ($rooms->isEmpty()) {
            return ApiResponse::success($rooms);
        }

        $roomIds = $rooms->pluck('id')->map(fn ($id) => (int) $id)->all();
        [$lastByTarget, $unreadByTarget] = $this->buildMessageSummaryForTargets(
            ChatRoom::class,
            $roomIds,
            $user->id
        );

        $data = $rooms->map(function (ChatRoom $room) use ($lastByTarget, $unreadByTarget) {
            $lastMessage = $lastByTarget[(int) $room->id] ?? null;

            return [
                'id' => (int) $room->id,
                'name' => $room->name,
                'avatar' => $room->avatar,
                'created_by' => (int) $room->created_by,
                'creator' => $room->creator,
                'users' => $room->users,
                'last_message' => $lastMessage ? $this->messagePreview($lastMessage) : null,
                'unread_count' => (int) ($unreadByTarget[(int) $room->id] ?? 0),
            ];
        });

        return ApiResponse::success($data);
    }

    public function presence(Request $request): JsonResponse
    {
        $onlineSince = now()->subMinutes(2);
        $users = User::query()
            ->select(['id', 'name', 'avatar', 'last_seen_at'])
            ->where('last_seen_at', '>=', $onlineSince)
            ->orderBy('name')
            ->get();

        return ApiResponse::success($users->map(fn (User $user) => [
            'id' => (int) $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'last_seen_at' => optional($user->last_seen_at)?->toISOString(),
            'status' => 'online',
        ]));
    }

    public function setPresenceStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:online,offline'],
        ]);

        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $status = $validated['status'];
        $user->forceFill([
            'status' => $status,
            'last_seen_at' => $status === 'online' ? now() : $user->last_seen_at,
        ])->saveQuietly();

        return ApiResponse::success([
            'status' => $user->status,
            'last_seen_at' => optional($user->last_seen_at)?->toISOString(),
        ]);
    }

    public function storeRoom(StoreChatRoomRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $room = ChatRoom::query()->create([
            'name' => $validated['name'],
            'avatar' => $validated['avatar'] ?? null,
            'created_by' => $user->id,
        ]);

        $memberIds = collect($validated['user_ids'] ?? [])
            ->push($user->id)
            ->unique()
            ->values();

        $syncPayload = [];
        foreach ($memberIds as $memberId) {
            $syncPayload[(int) $memberId] = [
                'role' => (int) $memberId === (int) $user->id ? 'owner' : 'member',
            ];
        }
        $room->users()->sync($syncPayload);

        return ApiResponse::success(
            $room->load(['creator:id,name,email,avatar,status', 'users:id,name,email,avatar,status']),
            'Sala criada com sucesso.',
            201
        );
    }

    public function updateRoom(ChatRoom $room, UpdateChatRoomRequest $request): JsonResponse
    {
        if (! $this->canManageRoom($request->user(), $room)) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $room->fill($request->validated());
        $room->save();

        return ApiResponse::success(
            $room->load(['creator:id,name,email,avatar,status', 'users:id,name,email,avatar,status']),
            'Sala atualizada com sucesso.'
        );
    }

    public function inviteUser(ChatRoom $room, InviteRoomUserRequest $request): JsonResponse
    {
        if (! $this->canManageRoom($request->user(), $room)) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $userId = (int) $request->validated('user_id');
        $room->users()->syncWithoutDetaching([
            $userId => ['role' => 'member'],
        ]);

        return ApiResponse::success(null, 'Utilizador convidado com sucesso.');
    }

    public function removeUser(ChatRoom $room, User $user, Request $request): JsonResponse
    {
        if (! $this->canManageRoom($request->user(), $room)) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $room->users()->detach($user->id);

        return ApiResponse::success(null, 'Utilizador removido da sala.');
    }

    public function destroyRoom(ChatRoom $room, Request $request): JsonResponse
    {
        $authUser = $request->user();
        if (! $authUser) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        // Admin/creator pode apagar sala inteira; membro comum apenas sai da sala.
        if ($this->canManageRoom($authUser, $room)) {
            Message::query()
                ->where('messageable_type', ChatRoom::class)
                ->where('messageable_id', $room->id)
                ->delete();
            $room->delete();

            return ApiResponse::success(null, 'Sala removida com sucesso.');
        }

        $isMember = $room->users()->where('users.id', $authUser->id)->exists();
        if (! $isMember) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $room->users()->detach($authUser->id);

        return ApiResponse::success(null, 'Saiu da sala com sucesso.');
    }

    public function destroyConversation(DirectConversation $conversation, Request $request): JsonResponse
    {
        $authUser = $request->user();
        if (! $authUser) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $isParticipant = $this->canAccessConversation($conversation, $authUser->id);
        $isAdmin = $authUser->hasRole('Admin');
        if (! $isParticipant && ! $isAdmin) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        Message::query()
            ->where('messageable_type', DirectConversation::class)
            ->where('messageable_id', $conversation->id)
            ->delete();
        $conversation->delete();

        return ApiResponse::success(null, 'Conversa removida com sucesso.');
    }

    public function startDirect(User $user, Request $request): JsonResponse
    {
        $authUser = $request->user();

        if ($authUser->id === $user->id) {
            return ApiResponse::error('Não é possível iniciar conversa consigo mesmo.', 422);
        }

        [$oneId, $twoId] = $authUser->id < $user->id
            ? [$authUser->id, $user->id]
            : [$user->id, $authUser->id];

        $conversation = DirectConversation::query()->firstOrCreate([
            'user_one_id' => $oneId,
            'user_two_id' => $twoId,
        ]);

        return ApiResponse::success(
            $conversation->load(['userOne:id,name,email,avatar,status', 'userTwo:id,name,email,avatar,status'])
        );
    }

    public function directMessages(DirectConversation $conversation, Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $this->canAccessConversation($conversation, $user->id)) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $conversationKey = $this->conversationKey('direct', (int) $conversation->id);
        $lastReadMessageId = $this->getEffectiveLastReadMessageId(
            (int) $user->id,
            $conversationKey,
            DirectConversation::class,
            (int) $conversation->id
        );

        $messages = Message::query()
            ->with('user:id,name,email,avatar,status')
            ->where('messageable_type', DirectConversation::class)
            ->where('messageable_id', $conversation->id)
            ->orderBy('created_at')
            ->get();

        $latestMessageId = (int) ($messages->last()?->id ?? 0);
        if ($latestMessageId > 0) {
            $this->upsertConversationRead((int) $user->id, $conversationKey, $latestMessageId);
        }

        return ApiResponse::success([
            'conversation' => $conversation->load(['userOne:id,name,email,avatar,status', 'userTwo:id,name,email,avatar,status']),
            'messages' => $this->appendSeenFlag($messages, $lastReadMessageId),
        ]);
    }

    public function roomMessages(ChatRoom $room, Request $request): JsonResponse
    {
        $user = $request->user();
        $isMember = $room->users()->where('users.id', $user->id)->exists();
        if (! $isMember) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $conversationKey = $this->conversationKey('room', (int) $room->id);
        $lastReadMessageId = $this->getEffectiveLastReadMessageId(
            (int) $user->id,
            $conversationKey,
            ChatRoom::class,
            (int) $room->id
        );

        $messages = Message::query()
            ->with('user:id,name,email,avatar,status')
            ->where('messageable_type', ChatRoom::class)
            ->where('messageable_id', $room->id)
            ->orderBy('created_at')
            ->get();

        $latestMessageId = (int) ($messages->last()?->id ?? 0);
        if ($latestMessageId > 0) {
            $this->upsertConversationRead((int) $user->id, $conversationKey, $latestMessageId);
        }

        return ApiResponse::success([
            'room' => $room->load(['creator:id,name,email,avatar,status', 'users:id,name,email,avatar,status']),
            'messages' => $this->appendSeenFlag($messages, $lastReadMessageId),
        ]);
    }

    public function storeMessage(StoreMessageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $target = $validated['target_type'] === 'room'
            ? ChatRoom::query()->findOrFail((int) $validated['target_id'])
            : DirectConversation::query()->findOrFail((int) $validated['target_id']);

        $message = Message::query()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
            'type' => $validated['type'] ?? 'text',
            'messageable_type' => $target::class,
            'messageable_id' => $target->id,
        ]);

        $message->load('user:id,name,email,avatar,status');
        MessageRead::query()->firstOrCreate([
            'message_id' => $message->id,
            'user_id' => $user->id,
        ], [
            'read_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return ApiResponse::success($message, 'Mensagem enviada.', 201);
    }

    public function updateMessage(Message $message, Request $request): JsonResponse
    {
        $authUser = $request->user();
        if (! $this->canManageMessage($authUser, $message)) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $message->body = $validated['body'];
        $message->save();
        $message->load('user:id,name,email,avatar,status');

        return ApiResponse::success($message, 'Mensagem atualizada com sucesso.');
    }

    public function destroyMessage(Message $message, Request $request): JsonResponse
    {
        $authUser = $request->user();
        if (! $this->canManageMessage($authUser, $message)) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $message->delete();

        return ApiResponse::success(null, 'Mensagem removida com sucesso.');
    }

    public function markAsRead(MarkMessagesReadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $messageableType = $validated['target_type'] === 'room'
            ? ChatRoom::class
            : DirectConversation::class;

        $messages = Message::query()
            ->where('messageable_type', $messageableType)
            ->where('messageable_id', (int) $validated['target_id'])
            ->where('user_id', '!=', $user->id)
            ->get(['id', 'read_at']);

        $now = now();
        $readRows = $messages->map(fn (Message $message) => [
            'message_id' => (int) $message->id,
            'user_id' => (int) $user->id,
            'read_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if (! empty($readRows)) {
            MessageRead::query()->upsert(
                $readRows,
                ['message_id', 'user_id'],
                ['read_at', 'updated_at']
            );
        }

        Message::query()
            ->whereIn('id', $messages->pluck('id')->all())
            ->whereNull('read_at')
            ->update(['read_at' => $now]);

        $latestMessageId = (int) (
            Message::query()
                ->where('messageable_type', $messageableType)
                ->where('messageable_id', (int) $validated['target_id'])
                ->max('id') ?? 0
        );
        if ($latestMessageId > 0) {
            $this->upsertConversationRead(
                (int) $user->id,
                $this->conversationKey($validated['target_type'], (int) $validated['target_id']),
                $latestMessageId
            );
        }

        return ApiResponse::success(null, 'Mensagens marcadas como lidas.');
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $directConversationIds = DirectConversation::query()
            ->where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $roomIds = ChatRoom::query()
            ->whereHas('users', fn ($query) => $query->where('users.id', $user->id))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $readsByConversation = $this->getEffectiveLastReadByConversation(
            (int) $user->id,
            DirectConversation::class,
            $directConversationIds,
            'direct'
        );
        $readsByConversation += $this->getEffectiveLastReadByConversation(
            (int) $user->id,
            ChatRoom::class,
            $roomIds,
            'room'
        );

        $counts = [];

        $directMessages = Message::query()
            ->where('messageable_type', DirectConversation::class)
            ->whereIn('messageable_id', $directConversationIds)
            ->where('user_id', '!=', $user->id)
            ->get(['id', 'messageable_id']);
        foreach ($directMessages as $message) {
            $conversationKey = $this->conversationKey('direct', (int) $message->messageable_id);
            $lastReadMessageId = (int) ($readsByConversation[$conversationKey] ?? 0);
            if ((int) $message->id <= $lastReadMessageId) {
                continue;
            }
            $counts[$conversationKey] = (int) ($counts[$conversationKey] ?? 0) + 1;
        }

        $roomMessages = Message::query()
            ->where('messageable_type', ChatRoom::class)
            ->whereIn('messageable_id', $roomIds)
            ->where('user_id', '!=', $user->id)
            ->get(['id', 'messageable_id']);
        foreach ($roomMessages as $message) {
            $conversationKey = $this->conversationKey('room', (int) $message->messageable_id);
            $lastReadMessageId = (int) ($readsByConversation[$conversationKey] ?? 0);
            if ((int) $message->id <= $lastReadMessageId) {
                continue;
            }
            $counts[$conversationKey] = (int) ($counts[$conversationKey] ?? 0) + 1;
        }

        $total = array_sum($counts);

        return ApiResponse::success([
            'total' => (int) $total,
            'conversations' => $counts,
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        /** @var UploadedFile $image */
        $image = $validated['image'];
        $path = $image->store('chat', 'public');

        return ApiResponse::success([
            'path' => $path,
            'url' => asset('storage/'.$path),
        ], 'Imagem enviada com sucesso.');
    }

    private function canAccessConversation(DirectConversation $conversation, int $userId): bool
    {
        return $conversation->user_one_id === $userId || $conversation->user_two_id === $userId;
    }

    private function canManageRoom(?User $user, ChatRoom $room): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        return (int) $room->created_by === (int) $user->id;
    }

    private function canManageMessage(?User $user, Message $message): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        return (int) $message->user_id === (int) $user->id;
    }

    private function isOnline($lastSeenAt): bool
    {
        return $lastSeenAt !== null && $lastSeenAt->greaterThanOrEqualTo(now()->subMinutes(2));
    }

    private function buildMessageSummaryForTargets(string $messageableType, array $targetIds, int $authUserId): array
    {
        if (empty($targetIds)) {
            return [[], []];
        }

        $messages = Message::query()
            ->where('messageable_type', $messageableType)
            ->whereIn('messageable_id', $targetIds)
            ->orderByDesc('created_at')
            ->get();

        $lastByTarget = [];
        foreach ($messages as $message) {
            $targetId = (int) $message->messageable_id;
            if (! isset($lastByTarget[$targetId])) {
                $lastByTarget[$targetId] = $message;
            }
        }

        $targetType = $messageableType === ChatRoom::class ? 'room' : 'direct';
        $conversationKeys = [];
        foreach ($targetIds as $targetId) {
            $conversationKeys[(int) $targetId] = $this->conversationKey($targetType, (int) $targetId);
        }

        $readsByConversation = $this->getEffectiveLastReadByConversation(
            $authUserId,
            $messageableType,
            $targetIds,
            $targetType
        );

        $candidateMessages = Message::query()
            ->where('messageable_type', $messageableType)
            ->whereIn('messageable_id', $targetIds)
            ->where('user_id', '!=', $authUserId)
            ->get(['id', 'messageable_id']);

        $unreadByTarget = [];
        foreach ($candidateMessages as $message) {
            $targetId = (int) $message->messageable_id;
            $conversationKey = $conversationKeys[$targetId] ?? null;
            if (! $conversationKey) {
                continue;
            }
            $lastReadMessageId = (int) ($readsByConversation[$conversationKey] ?? 0);
            if ((int) $message->id <= $lastReadMessageId) {
                continue;
            }
            $unreadByTarget[$targetId] = (int) ($unreadByTarget[$targetId] ?? 0) + 1;
        }

        return [$lastByTarget, $unreadByTarget];
    }

    private function conversationKey(string $targetType, int $targetId): string
    {
        return "{$targetType}:{$targetId}";
    }

    private function getLastReadMessageId(int $userId, string $conversationKey): int
    {
        if (! $this->hasConversationReadsTable()) {
            return 0;
        }

        return (int) (ChatConversationRead::query()
            ->where('user_id', $userId)
            ->where('conversation_id', $conversationKey)
            ->value('last_read_message_id') ?? 0);
    }

    private function getEffectiveLastReadMessageId(
        int $userId,
        string $conversationKey,
        string $messageableType,
        int $messageableId
    ): int {
        $backendLastRead = $this->getLastReadMessageId($userId, $conversationKey);
        if ($backendLastRead > 0) {
            return $backendLastRead;
        }

        $legacyLastRead = MessageRead::query()
            ->where('user_id', $userId)
            ->whereHas('message', function ($query) use ($messageableType, $messageableId) {
                $query->where('messageable_type', $messageableType)
                    ->where('messageable_id', $messageableId);
            })
            ->max('message_id');

        return (int) ($legacyLastRead ?? 0);
    }

    private function getEffectiveLastReadByConversation(
        int $userId,
        string $messageableType,
        array $targetIds,
        string $targetType
    ): array {
        if (empty($targetIds)) {
            return [];
        }

        $conversationKeys = [];
        foreach ($targetIds as $targetId) {
            $conversationKeys[(int) $targetId] = $this->conversationKey($targetType, (int) $targetId);
        }

        $backendReads = [];
        if ($this->hasConversationReadsTable()) {
            $backendReads = ChatConversationRead::query()
                ->where('user_id', $userId)
                ->whereIn('conversation_id', array_values($conversationKeys))
                ->pluck('last_read_message_id', 'conversation_id')
                ->map(fn ($value) => (int) ($value ?? 0))
                ->toArray();
        }

        $legacyReads = MessageRead::query()
            ->join('messages', 'messages.id', '=', 'message_reads.message_id')
            ->where('message_reads.user_id', $userId)
            ->where('messages.messageable_type', $messageableType)
            ->whereIn('messages.messageable_id', $targetIds)
            ->groupBy('messages.messageable_id')
            ->selectRaw('messages.messageable_id as target_id, max(message_reads.message_id) as last_read_message_id')
            ->get();

        foreach ($legacyReads as $legacyRead) {
            $targetId = (int) $legacyRead->target_id;
            $conversationKey = $conversationKeys[$targetId] ?? null;
            if (! $conversationKey) {
                continue;
            }
            if (isset($backendReads[$conversationKey]) && (int) $backendReads[$conversationKey] > 0) {
                continue;
            }
            $backendReads[$conversationKey] = (int) ($legacyRead->last_read_message_id ?? 0);
        }

        return $backendReads;
    }

    private function upsertConversationRead(int $userId, string $conversationKey, int $lastReadMessageId): void
    {
        if (! $this->hasConversationReadsTable()) {
            return;
        }

        ChatConversationRead::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'conversation_id' => $conversationKey,
            ],
            [
                'last_read_message_id' => $lastReadMessageId,
            ]
        );
    }

    private function hasConversationReadsTable(): bool
    {
        if ($this->conversationReadsTableAvailable !== null) {
            return $this->conversationReadsTableAvailable;
        }

        return $this->conversationReadsTableAvailable = Schema::hasTable('chat_conversation_reads');
    }

    private function appendSeenFlag(EloquentCollection $messages, int $lastReadMessageId): EloquentCollection
    {
        return $messages->map(function (Message $message) use ($lastReadMessageId) {
            $message->setAttribute('is_seen', (int) $message->id <= $lastReadMessageId);

            return $message;
        });
    }

    private function messagePreview(Message $message): array
    {
        $message->loadMissing('user:id,name,email,avatar');

        return [
            'id' => (int) $message->id,
            'body' => $message->body,
            'type' => $message->type,
            'created_at' => optional($message->created_at)?->toISOString(),
            'user' => [
                'id' => (int) $message->user_id,
                'name' => $message->user?->name,
                'email' => $message->user?->email,
                'avatar' => $message->user?->avatar,
            ],
        ];
    }
}
