<?php

use App\Models\ChatRoom;
use App\Models\DirectConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat', function ($user) {
    return [
        'id' => (int) $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{id}', function ($user, $id) {
    return DirectConversation::query()
        ->whereKey((int) $id)
        ->where(function ($query) use ($user) {
            $query->where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id);
        })
        ->exists();
});

Broadcast::channel('room.{id}', function ($user, $id) {
    return ChatRoom::query()
        ->whereKey((int) $id)
        ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
        ->exists();
});
