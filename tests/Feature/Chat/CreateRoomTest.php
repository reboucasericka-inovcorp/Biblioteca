<?php

use App\Models\ChatRoom;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('admin cria sala com sucesso', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/chat/rooms', [
        'name' => 'Room QA',
        'avatar' => 'https://example.com/room.png',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Room QA');

    $room = ChatRoom::query()->where('name', 'Room QA')->first();
    expect($room)->not->toBeNull();
    expect($room->users()->where('users.id', $admin->id)->exists())->toBeTrue();
});

test('user comum nao pode criar sala', function () {
    $user = User::factory()->create();
    $user->assignRole('Cidadao');

    Sanctum::actingAs($user);

    $this->postJson('/api/chat/rooms', [
        'name' => 'Sala Proibida',
    ])->assertForbidden();
});
