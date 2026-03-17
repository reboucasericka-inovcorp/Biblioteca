<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GoogleBooksImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_pode_importar_livro_do_google_books(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes/*' => Http::response([
                'id' => 'vol-123',
                'volumeInfo' => [
                    'title' => 'Refactoring',
                    'authors' => ['Martin Fowler', 'Kent Beck'],
                    'publisher' => 'Addison-Wesley',
                    'publishedDate' => '2018',
                    'description' => 'Improving the design of existing code.',
                    'industryIdentifiers' => [
                        ['type' => 'ISBN_13', 'identifier' => '9780134757599'],
                    ],
                    'imageLinks' => [
                        'thumbnail' => 'https://example.com/refactoring.jpg',
                    ],
                ],
            ], 200),
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/google-books/import', [
            'google_volume_id' => 'vol-123',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'google_volume_id' => 'vol-123',
            'name' => 'Refactoring',
        ]);

        $this->assertDatabaseHas('authors', ['name' => 'Martin Fowler']);
        $this->assertDatabaseHas('authors', ['name' => 'Kent Beck']);

        $bookId = (int) $response->json('data.id');
        $this->assertDatabaseHas('author_book', [
            'book_id' => $bookId,
        ]);
    }
}
