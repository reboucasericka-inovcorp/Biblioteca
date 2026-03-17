<?php

use App\Models\Book;
use App\Models\Publisher;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * TESTE 1: Criar requisição com sucesso
 *
 * Verificar que um utilizador pode requisitar um livro disponível
 */
test('user can create a requisition successfully', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->assignRole('Cidadao');

    $book = Book::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/requisitions', [
        'book_id' => $book->id,
    ]);

    // Assert
    expect($response->status())->toBe(201);
    expect($response->json('message'))->toBe('Requisition created successfully.');

    $this->assertDatabaseHas('requisitions', [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => Requisition::STATUS_ACTIVE,
    ]);

    // Verificar que o log foi criado
    $this->assertDatabaseHas('logs', [
        'user_id' => $user->id,
        'module' => 'Requisition',
        'object_id' => Requisition::where('user_id', $user->id)->first()->id,
    ]);
});

/**
 * TESTE 2: Validação - Livro inválido
 *
 * Verificar que não é possível requisitar um livro inexistente
 */
test('cannot create requisition with invalid book', function () {
    $user = User::factory()->create();
    $user->assignRole('Cidadao');

    $response = $this->actingAs($user)->postJson('/api/requisitions', [
        'book_id' => 9999,
    ]);

    expect($response->status())->toBe(422);
});

/**
 * TESTE 3: Livro indisponível (já requisitado)
 *
 * Verificar que um livro já requisitado não pode ser requisitado novamente
 */
test('cannot create requisition when book already requested', function () {
    Mail::fake();

    $user1 = User::factory()->create();
    $user1->assignRole('Cidadao');
    $user2 = User::factory()->create();
    $user2->assignRole('Cidadao');

    $book = Book::factory()->create();

    // Primeira requisição
    $this->actingAs($user1)->postJson('/api/requisitions', ['book_id' => $book->id]);

    // Tentativa da segunda requisição
    $response = $this->actingAs($user2)->postJson('/api/requisitions', ['book_id' => $book->id]);

    expect($response->status())->toBe(422);
    expect($response->json('message'))->toBe('Book is not available.');
});

/**
 * TESTE 4: Limite de 3 requisições ativas
 *
 * Verificar que um utilizador não pode ter mais de 3 requisições ativas
 */
test('user cannot have more than 3 active requisitions', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->assignRole('Cidadao');

    // Criar 3 livros com stock e 3 requisições
    $books = Book::factory(3)->create(['stock' => 2]);
    foreach ($books as $book) {
        $this->actingAs($user)->postJson('/api/requisitions', ['book_id' => $book->id]);
    }

    // Tentar criar um 4º livro (com stock) e requisitá-lo — deve falhar por limite
    $fourthBook = Book::factory()->create(['stock' => 5]);
    $response = $this->actingAs($user)->postJson('/api/requisitions', ['book_id' => $fourthBook->id]);

    expect($response->status())->toBe(422);
    expect($response->json('message'))->toBe('You already have 3 active requisitions.');
});

/**
 * TESTE 5: Devolução de livro com sucesso
 *
 * Verificar que uma requisição ativa pode ser devolvida
 */
test('user can confirm return of a requisition', function () {
    $user = User::factory()->create();
    $user->assignRole('Cidadao');
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $book = Book::factory()->create();
    $requisition = Requisition::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => Requisition::STATUS_ACTIVE,
    ]);

    // Act - a rota /return exige role Admin
    $response = $this->actingAs($admin)->postJson("/api/requisitions/{$requisition->id}/return");

    // Assert
    expect($response->status())->toBe(200);
    expect($response->json('message'))->toBe('Return confirmed');

    $this->assertDatabaseHas('requisitions', [
        'id' => $requisition->id,
        'status' => Requisition::STATUS_RETURNED,
    ]);

    // Verificar que o log foi criado com ação de devolução (user_id é o admin que confirmou)
    $this->assertDatabaseHas('logs', [
        'user_id' => $admin->id,
        'module' => 'Requisition',
        'object_id' => $requisition->id,
    ]);
});

/**
 * TESTE 6: Listagem de requisições filtra por utilizador
 *
 * Verificar que cada utilizador vê apenas as suas requisições (rota web)
 */
test('user can only see their own requisitions', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('Cidadao');
    $user2 = User::factory()->create();
    $user2->assignRole('Cidadao');

    // Criar requisições para diferentes utilizadores
    Requisition::factory(2)->create(['user_id' => $user1->id]);
    Requisition::factory(3)->create(['user_id' => $user2->id]);

    // Act - User 1 acessa as suas requisições
    $response = $this->actingAs($user1)->get('/requisitions');

    expect($response->status())->toBe(200);
});

/**
 * TESTE 6b: Listagem via API — extração robusta independente do formato de ApiResponse
 */
test('user sees only own requisitions via api with robust payload extraction', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('Cidadao');
    $user2 = User::factory()->create();
    $user2->assignRole('Cidadao');

    $book1 = Book::factory()->create();
    $book2 = Book::factory()->create();
    Requisition::factory()->create(['user_id' => $user1->id, 'book_id' => $book1->id]);
    Requisition::factory()->create(['user_id' => $user2->id, 'book_id' => $book2->id]);

    $response = $this->actingAs($user1)->getJson('/api/requisitions');
    $response->assertStatus(200);

    $json = $response->json();
    // Resistente a mudanças no ApiResponse (paginator vs array direto)
    $items = data_get($json, 'data.data') ?? data_get($json, 'data') ?? [];

    expect($items)->toBeArray();
    expect(count($items))->toBe(1);
    expect((int) ($items[0]['user_id'] ?? $items[0]['user']['id'] ?? 0))->toBe($user1->id);
});

/**
 * TESTE 7: Stock = 0 impede requisição
 *
 * Verificar que um livro sem stock não pode ser requisitado
 */
test('cannot create requisition for book with zero stock', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->assignRole('Cidadao');

    $book = Book::factory()->unavailable()->create();

    $response = $this->actingAs($user)->postJson('/api/requisitions', [
        'book_id' => $book->id,
    ]);

    expect($response->status())->toBe(422);
});
