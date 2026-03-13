<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_home_shows_welcome_page_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }

    public function test_home_shows_welcome_page_for_authenticated_users(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
    }
}   