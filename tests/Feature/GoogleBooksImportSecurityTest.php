<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleBooksImportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_requer_autenticacao(): void
    {
        $response = $this->postJson('/api/google-books/import', [
            'google_volume_id' => 'abc123',
        ]);

        $response->assertStatus(401);
    }

    public function test_import_requer_role_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/google-books/import', [
                'google_volume_id' => 'abc123',
            ]);

        $response->assertStatus(403);
    }
}
