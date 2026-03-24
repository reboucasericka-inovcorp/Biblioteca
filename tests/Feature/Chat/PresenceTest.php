<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('presence lista apenas utilizadores online', function () {
    $viewer = User::factory()->create(['last_seen_at' => now()]);
    $viewer->assignRole('Cidadao');

    $online = User::factory()->create(['last_seen_at' => now()->subMinute()]);
    $online->assignRole('Cidadao');

    $offline = User::factory()->create(['last_seen_at' => now()->subMinutes(5)]);
    $offline->assignRole('Cidadao');

    Sanctum::actingAs($viewer);

    $response = $this->getJson('/api/chat/presence')->assertOk();
    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toContain($online->id);
    expect($ids)->not->toContain($offline->id);
});

test('presence status endpoint atualiza estado online e offline', function () {
    $user = User::factory()->create([
        'status' => 'offline',
        'last_seen_at' => now()->subMinutes(10),
    ]);
    $user->assignRole('Cidadao');

    Sanctum::actingAs($user);

    $this->postJson('/api/chat/presence/status', ['status' => 'online'])
        ->assertOk()
        ->assertJsonPath('data.status', 'online');

    $user->refresh();
    expect($user->status)->toBe('online');
    expect($user->last_seen_at)->not->toBeNull();

    $this->postJson('/api/chat/presence/status', ['status' => 'offline'])
        ->assertOk()
        ->assertJsonPath('data.status', 'offline');

    $user->refresh();
    expect($user->status)->toBe('offline');
});
