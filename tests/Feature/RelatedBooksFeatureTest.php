<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RelatedBooksFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_livros_com_palavras_semelhantes_aparecem_como_relacionados(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $publisher = Publisher::create(['name' => 'Tech Publisher']);
        $author = Author::create(['name' => 'Autor Principal']);

        $currentBook = Book::create([
            'name' => 'Laravel Clean Architecture',
            'isbn' => '978-0-11-111111-1',
            'price' => 25.90,
            'publisher_id' => $publisher->id,
            'bibliography' => 'Arquitetura limpa em Laravel com padrões de projeto, serviços e testes.',
        ]);
        $currentBook->authors()->attach($author->id);

        $relatedBook = Book::create([
            'name' => 'Padrões em Laravel',
            'isbn' => '978-0-22-222222-2',
            'price' => 20.00,
            'publisher_id' => $publisher->id,
            'bibliography' => 'Este livro cobre laravel, arquitetura limpa, serviços e testes automatizados.',
        ]);
        $relatedBook->authors()->attach($author->id);

        $unrelatedBook = Book::create([
            'name' => 'Jardinagem para Iniciantes',
            'isbn' => '978-0-33-333333-3',
            'price' => 12.00,
            'publisher_id' => $publisher->id,
            'bibliography' => 'Plantas, sementes, solo e adubação para o jardim doméstico.',
        ]);
        $unrelatedBook->authors()->attach($author->id);

        $response = $this->getJson("/api/books/{$currentBook->id}");

        $response->assertOk();
        $response->assertJsonPath('data.related_books.0.id', $relatedBook->id);
        $response->assertJsonMissing(['id' => $unrelatedBook->id, 'title' => 'Jardinagem para Iniciantes']);
    }

    public function test_livros_sem_intersecao_nao_aparecem_como_relacionados(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $publisher = Publisher::create(['name' => 'General Publisher']);
        $author = Author::create(['name' => 'Autor Dois']);

        $currentBook = Book::create([
            'name' => 'Compiladores Modernos',
            'isbn' => '978-0-44-444444-4',
            'price' => 30.00,
            'publisher_id' => $publisher->id,
            'bibliography' => 'Análise léxica, sintática, semântica e geração de código.',
        ]);
        $currentBook->authors()->attach($author->id);

        $bookWithoutIntersection = Book::create([
            'name' => 'Culinária Mediterrânea',
            'isbn' => '978-0-55-555555-5',
            'price' => 18.50,
            'publisher_id' => $publisher->id,
            'bibliography' => 'Receitas com azeite, peixes, massas e ervas aromáticas.',
        ]);
        $bookWithoutIntersection->authors()->attach($author->id);

        $response = $this->getJson("/api/books/{$currentBook->id}");

        $response->assertOk();
        $response->assertJsonCount(0, 'data.related_books');
    }
}
