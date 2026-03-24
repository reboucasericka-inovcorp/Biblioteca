<?php

use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('unread_count em direto considera message_reads', function () {
    $alice = User::factory()->create();
    $alice->assignRole('Cidadao');

    $bob = User::factory()->create();
    $bob->assignRole('Cidadao');

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($alice->id, $bob->id),
        'user_two_id' => max($alice->id, $bob->id),
    ]);

    $message = Message::query()->create([
        'user_id' => $bob->id,
        'body' => 'Ainda não lida',
        'type' => 'text',
        'messageable_type' => DirectConversation::class,
        'messageable_id' => $conversation->id,
    ]);

    Sanctum::actingAs($alice);

    $response = $this->getJson('/api/chat/users')->assertOk();
    $bobPayload = collect($response->json('data'))->firstWhere('id', $bob->id);
    expect((int) ($bobPayload['unread_count'] ?? 0))->toBe(1);

    $this->postJson('/api/chat/messages/read', [
        'target_type' => 'conversation',
        'target_id' => $conversation->id,
    ])->assertOk();

    $response = $this->getJson('/api/chat/users')->assertOk();
    $bobPayload = collect($response->json('data'))->firstWhere('id', $bob->id);
    expect((int) ($bobPayload['unread_count'] ?? 0))->toBe(0);

    $this->assertDatabaseHas('message_reads', [
        'message_id' => $message->id,
        'user_id' => $alice->id,
    ]);
});

test('unread_count em sala ignora mensagens do proprio user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $member = User::factory()->create();
    $member->assignRole('Cidadao');

    $room = ChatRoom::query()->create([
        'name' => 'Geral',
        'created_by' => $admin->id,
    ]);
    $room->users()->sync([
        $admin->id => ['role' => 'owner'],
        $member->id => ['role' => 'member'],
    ]);

    Message::query()->create([
        'user_id' => $member->id,
        'body' => 'Minha própria mensagem',
        'type' => 'text',
        'messageable_type' => ChatRoom::class,
        'messageable_id' => $room->id,
    ]);
    Message::query()->create([
        'user_id' => $admin->id,
        'body' => 'Mensagem do admin',
        'type' => 'text',
        'messageable_type' => ChatRoom::class,
        'messageable_id' => $room->id,
    ]);

    Sanctum::actingAs($member);

    $response = $this->getJson('/api/chat/rooms')->assertOk();
    $roomPayload = collect($response->json('data'))->firstWhere('id', $room->id);
    expect((int) ($roomPayload['unread_count'] ?? 0))->toBe(1);
});
