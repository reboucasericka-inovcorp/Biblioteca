<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteRoomUserRequest;
use App\Http\Requests\MarkMessagesReadRequest;
use App\Http\Requests\StoreChatRoomRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

class ChatApiController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $query = User::query()
            ->select(['id', 'name', 'email', 'avatar', 'status'])
            ->where('id', '!=', $authUser->id);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return ApiResponse::success($query->orderBy('name')->get());
    }

    public function rooms(Request $request): JsonResponse
    {
        $user = $request->user();

        $rooms = ChatRoom::query()
            ->with(['creator:id,name,email,avatar,status', 'users:id,name,email,avatar,status'])
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->orderBy('name')
            ->get();

        return ApiResponse::success($rooms);
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

        $room->users()->sync($memberIds);

        return ApiResponse::success(
            $room->load(['creator:id,name,email,avatar,status', 'users:id,name,email,avatar,status']),
            'Sala criada com sucesso.',
            201
        );
    }

    public function inviteUser(ChatRoom $room, InviteRoomUserRequest $request): JsonResponse
    {
        $userId = (int) $request->validated('user_id');
        $room->users()->syncWithoutDetaching([$userId]);

        return ApiResponse::success(null, 'Utilizador convidado com sucesso.');
    }

    public function removeUser(ChatRoom $room, User $user, Request $request): JsonResponse
    {
        if (! $request->user()?->hasRole('Admin')) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $room->users()->detach($user->id);

        return ApiResponse::success(null, 'Utilizador removido da sala.');
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

        $messages = Message::query()
            ->with('user:id,name,email,avatar,status')
            ->where('messageable_type', DirectConversation::class)
            ->where('messageable_id', $conversation->id)
            ->orderBy('created_at')
            ->get();

        return ApiResponse::success([
            'conversation' => $conversation->load(['userOne:id,name,email,avatar,status', 'userTwo:id,name,email,avatar,status']),
            'messages' => $messages,
        ]);
    }

    public function roomMessages(ChatRoom $room, Request $request): JsonResponse
    {
        $user = $request->user();
        $isMember = $room->users()->where('users.id', $user->id)->exists();
        if (! $isMember) {
            return ApiResponse::error('Não autorizado.', 403);
        }

        $messages = Message::query()
            ->with('user:id,name,email,avatar,status')
            ->where('messageable_type', ChatRoom::class)
            ->where('messageable_id', $room->id)
            ->orderBy('created_at')
            ->get();

        return ApiResponse::success([
            'room' => $room->load(['creator:id,name,email,avatar,status', 'users:id,name,email,avatar,status']),
            'messages' => $messages,
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
        broadcast(new MessageSent($message))->toOthers();

        return ApiResponse::success($message, 'Mensagem enviada.', 201);
    }

    public function markAsRead(MarkMessagesReadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $messageableType = $validated['target_type'] === 'room'
            ? ChatRoom::class
            : DirectConversation::class;

        Message::query()
            ->where('messageable_type', $messageableType)
            ->where('messageable_id', (int) $validated['target_id'])
            ->whereNull('read_at')
            ->where('user_id', '!=', $user->id)
            ->update(['read_at' => now()]);

        return ApiResponse::success(null, 'Mensagens marcadas como lidas.');
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
}
