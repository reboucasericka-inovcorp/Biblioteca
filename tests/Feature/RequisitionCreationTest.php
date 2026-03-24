<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Publisher;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RequisitionCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createBook(array $overrides = []): Book
    {
        $publisher = Publisher::create(['name' => 'Test Publisher']);

        return Book::create(array_merge([
            'name' => 'Test Book',
            'isbn' => '978-0-00-000000-'.uniqid(),
            'price' => 10.00,
            'publisher_id' => $publisher->id,
            'stock' => 5,
        ], $overrides));
    }

    public function test_criar_requisicao_com_sucesso(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->assignRole('Cidadao');
        $book = $this->createBook();

        $response = $this->actingAs($user)->postJson('/api/requisitions', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Requisition created successfully.']);

        $this->assertDatabaseHas('requisitions', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => Requisition::STATUS_PENDING,
        ]);

        Mail::assertSent(\App\Mail\RequisitionCreated::class);
    }

    public function test_impedir_requisicao_se_livro_indisponivel(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->assignRole('Cidadao');
        $book = $this->createBook();

        Requisition::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => Requisition::STATUS_ACTIVE,
        ]);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('Cidadao');

        $countBefore = Requisition::count();

        $response = $this->actingAs($otherUser)->postJson('/api/requisitions', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Book is not available.']);

        $this->assertEquals($countBefore, Requisition::count());
        Mail::assertNotSent(\App\Mail\RequisitionCreated::class);
    }

    public function test_impedir_requisicao_se_utilizador_ja_tem_3_ativas(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        $books = [];
        for ($i = 0; $i < 3; $i++) {
            $books[] = $this->createBook();
            Requisition::create([
                'user_id' => $user->id,
                'book_id' => $books[$i]->id,
            ]);
        }

        $fourthBook = $this->createBook();
        $countBefore = Requisition::count();

        $response = $this->actingAs($user)->postJson('/api/requisitions', [
            'book_id' => $fourthBook->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'You already have 3 requisitions pending or active on loan.']);

        $this->assertEquals($countBefore, Requisition::count());
        Mail::assertNotSent(\App\Mail\RequisitionCreated::class);
    }

    public function test_excecao_livro_indisponivel_retorna_422(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cidadao');
        $book = $this->createBook();

        Requisition::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => Requisition::STATUS_ACTIVE,
        ]);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('Cidadao');

        $response = $this->actingAs($otherUser)->postJson('/api/requisitions', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data']);
        $this->assertNull($response->json('data'));
    }

    public function test_excecao_limite_3_ativas_retorna_422(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        for ($i = 0; $i < 3; $i++) {
            $book = $this->createBook();
            Requisition::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        $fourthBook = $this->createBook();

        $response = $this->actingAs($user)->postJson('/api/requisitions', [
            'book_id' => $fourthBook->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'data']);
        $this->assertNull($response->json('data'));
    }

    public function test_nenhuma_requisicao_criada_apos_excecao_rollback(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cidadao');
        $book = $this->createBook();

        Requisition::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => Requisition::STATUS_ACTIVE,
        ]);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('Cidadao');

        $countBefore = Requisition::count();

        $this->actingAs($otherUser)->postJson('/api/requisitions', [
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseCount('requisitions', $countBefore);
    }

    public function test_book_id_invalido_retorna_422_validacao(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        $response = $this->actingAs($user)->postJson('/api/requisitions', [
            'book_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['book_id']);
    }
}
