<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Cidadao']);
    }

    public function test_atualizar_role_com_sucesso(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        $response = $this->actingAs($admin)->patchJson("/api/users/{$user->id}/role", [
            'role' => 'Admin',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Role updated successfully.',
            'data' => ['id' => $user->id, 'role' => 'Admin'],
        ]);

        $user->refresh();
        $this->assertTrue($user->hasRole('Admin'));
    }

    public function test_role_invalida_retorna_422(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        $response = $this->actingAs($admin)->patchJson("/api/users/{$user->id}/role", [
            'role' => 'SuperAdmin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    public function test_role_obrigatorio_retorna_422(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->patchJson("/api/users/{$user->id}/role", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }
}
