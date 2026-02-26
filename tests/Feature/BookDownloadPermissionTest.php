<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Publisher;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookDownloadPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Cidadao']);
    }

    public function test_download_sem_requisicao_retorna_403(): void
    {
        Storage::fake('books');
        $publisher = Publisher::create(['name' => 'Test Publisher']);
        $book = Book::create([
            'name' => 'Test Book',
            'isbn' => '978-0-00-000000-0',
            'price' => 0,
            'publisher_id' => $publisher->id,
            'file_path' => 'test.pdf',
        ]);
        Storage::disk('books')->put('test.pdf', '%PDF-1.4 fake content');

        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        $response = $this->actingAs($user)->get("/books/{$book->id}/download");

        $response->assertStatus(403);
    }

    public function test_download_como_admin_retorna_200(): void
    {
        Storage::fake('books');
        $publisher = Publisher::create(['name' => 'Test Publisher']);
        $book = Book::create([
            'name' => 'Admin Test Book',
            'isbn' => '978-0-00-000000-1',
            'price' => 0,
            'publisher_id' => $publisher->id,
            'file_path' => 'admin-test.pdf',
        ]);
        Storage::disk('books')->put('admin-test.pdf', '%PDF-1.4 fake content');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get("/books/{$book->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_download_com_requisicao_ativa_retorna_200(): void
    {
        Storage::fake('books');
        $publisher = Publisher::create(['name' => 'Test Publisher']);
        $book = Book::create([
            'name' => 'Requisition Test Book',
            'isbn' => '978-0-00-000000-2',
            'price' => 0,
            'publisher_id' => $publisher->id,
            'file_path' => 'requisition-test.pdf',
        ]);
        Storage::disk('books')->put('requisition-test.pdf', '%PDF-1.4 fake content');

        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        Requisition::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->get("/books/{$book->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }
}
