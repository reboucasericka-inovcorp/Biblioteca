<?php

use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('mark as read cria registos na message_reads', function () {
    $reader = User::factory()->create();
    $reader->assignRole('Cidadao');

    $sender = User::factory()->create();
    $sender->assignRole('Cidadao');

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($reader->id, $sender->id),
        'user_two_id' => max($reader->id, $sender->id),
    ]);

    $message = Message::query()->create([
        'user_id' => $sender->id,
        'body' => 'Ler mensagem',
        'type' => 'text',
        'messageable_type' => DirectConversation::class,
        'messageable_id' => $conversation->id,
    ]);

    Sanctum::actingAs($reader);

    $this->postJson('/api/chat/messages/read', [
        'target_type' => 'conversation',
        'target_id' => $conversation->id,
    ])->assertOk();

    $this->assertDatabaseHas('message_reads', [
        'message_id' => $message->id,
        'user_id' => $reader->id,
    ]);
});
