<?php

use App\Models\ChatRoom;
use App\Models\DirectConversation;
use App\Models\Message;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('admin pode criar sala de chat', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $member = User::factory()->create();
    $member->assignRole('Cidadao');

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/chat/rooms', [
        'name' => 'Suporte',
        'user_ids' => [$member->id],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Suporte');

    $room = ChatRoom::query()->where('name', 'Suporte')->first();
    expect($room)->not->toBeNull();
    expect($room->users()->where('users.id', $member->id)->exists())->toBeTrue();
});

test('cidadao nao pode criar sala', function () {
    $citizen = User::factory()->create();
    $citizen->assignRole('Cidadao');

    Sanctum::actingAs($citizen);

    $this->postJson('/api/chat/rooms', [
        'name' => 'Sala Restrita',
    ])->assertForbidden();
});

test('api de presence retorna utilizadores online recentes', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole('Cidadao');

    $online = User::factory()->create([
        'last_seen_at' => now()->subMinute(),
    ]);
    $online->assignRole('Cidadao');

    $offline = User::factory()->create([
        'last_seen_at' => now()->subMinutes(10),
    ]);
    $offline->assignRole('Cidadao');

    Sanctum::actingAs($viewer);

    $response = $this->getJson('/api/chat/presence');
    $response->assertOk();

    $onlineIds = collect($response->json('data'))->pluck('id')->all();
    expect($onlineIds)->toContain($online->id);
    expect($onlineIds)->not->toContain($offline->id);
});

test('lista de utilizadores devolve last message e unread count no direto', function () {
    $alice = User::factory()->create();
    $alice->assignRole('Cidadao');

    $bob = User::factory()->create();
    $bob->assignRole('Cidadao');

    $conversation = DirectConversation::query()->create([
        'user_one_id' => min($alice->id, $bob->id),
        'user_two_id' => max($alice->id, $bob->id),
    ]);

    Message::query()->create([
        'user_id' => $bob->id,
        'body' => 'Olá Alice',
        'type' => 'text',
        'messageable_type' => DirectConversation::class,
        'messageable_id' => $conversation->id,
    ]);

    Sanctum::actingAs($alice);

    $response = $this->getJson('/api/chat/users');
    $response->assertOk();

    $bobPayload = collect($response->json('data'))->firstWhere('id', $bob->id);
    expect($bobPayload)->not->toBeNull();
    expect($bobPayload['last_message']['body'] ?? null)->toBe('Olá Alice');
    expect((int) ($bobPayload['unread_count'] ?? 0))->toBe(1);
});

test('lista de salas devolve last message e unread count', function () {
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
        'user_id' => $admin->id,
        'body' => 'Bem-vindo',
        'type' => 'text',
        'messageable_type' => ChatRoom::class,
        'messageable_id' => $room->id,
    ]);

    Sanctum::actingAs($member);

    $response = $this->getJson('/api/chat/rooms');
    $response->assertOk();

    $roomPayload = collect($response->json('data'))->firstWhere('id', $room->id);
    expect($roomPayload)->not->toBeNull();
    expect($roomPayload['last_message']['body'] ?? null)->toBe('Bem-vindo');
    expect((int) ($roomPayload['unread_count'] ?? 0))->toBe(1);
});
