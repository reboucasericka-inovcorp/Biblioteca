<?php

namespace App\Events;

use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->message->messageable_type === DirectConversation::class) {
            $channels[] = new PrivateChannel('conversation.'.$this->message->messageable_id);
        }

        if ($this->message->messageable_type === ChatRoom::class) {
            $channels[] = new PrivateChannel('room.'.$this->message->messageable_id);
        }

        foreach ($this->recipientUserIds() as $userId) {
            $channels[] = new PrivateChannel('user.'.$userId);
        }

        if (empty($channels)) {
            $channels[] = new PrivateChannel('conversation.0');
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $message = $this->message->loadMissing('user');
        $targetType = $message->messageable_type === ChatRoom::class ? 'room' : 'direct';

        return [
            'id' => $message->id,
            'user_id' => $message->user_id,
            'body' => $message->body,
            'type' => $message->type,
            'read_at' => optional($message->read_at)?->toISOString(),
            'created_at' => optional($message->created_at)?->toISOString(),
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'avatar' => $message->user->avatar,
                'email' => $message->user->email,
            ],
            'messageable_type' => $message->messageable_type,
            'messageable_id' => $message->messageable_id,
            'target_type' => $targetType,
            'target_id' => $message->messageable_id,
        ];
    }

    private function recipientUserIds(): array
    {
        if ($this->message->messageable_type === DirectConversation::class) {
            $conversation = DirectConversation::query()->find((int) $this->message->messageable_id);
            if (! $conversation) {
                return [];
            }

            return array_values(array_unique([
                (int) $conversation->user_one_id,
                (int) $conversation->user_two_id,
            ]));
        }

        if ($this->message->messageable_type === ChatRoom::class) {
            $room = ChatRoom::query()->find((int) $this->message->messageable_id);
            if (! $room) {
                return [];
            }

            return $room->users()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        }

        return [];
    }
}
