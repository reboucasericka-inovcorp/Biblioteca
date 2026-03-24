<?php

use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user envia mensagem direta e persiste no banco', function () {
    $sender = User::factory()->create();
    $sender->assignRole('Cidadao');

    $receiver = User::factory()->create();
    $receiver->assignRole('Cidadao');

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($sender->id, $receiver->id),
        'user_two_id' => max($sender->id, $receiver->id),
    ]);

    Sanctum::actingAs($sender);

    $response = $this->postJson('/api/chat/messages', [
        'target_type' => 'conversation',
        'target_id' => $conversation->id,
        'body' => 'Mensagem de teste',
        'type' => 'text',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.body', 'Mensagem de teste');

    $this->assertDatabaseHas('messages', [
        'messageable_type' => DirectConversation::class,
        'messageable_id' => $conversation->id,
        'user_id' => $sender->id,
        'body' => 'Mensagem de teste',
    ]);

    $message = Message::query()->where('messageable_id', $conversation->id)->first();
    $this->assertDatabaseHas('message_reads', [
        'message_id' => $message->id,
        'user_id' => $sender->id,
    ]);
});
