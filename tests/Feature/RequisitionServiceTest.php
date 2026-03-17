<?php

namespace Tests\Feature;

use App\Exceptions\BookUnavailableException;
use App\Exceptions\UserRequisitionLimitExceededException;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\Requisition;
use App\Models\User;
use App\Services\RequisitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequisitionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RequisitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->service = app(RequisitionService::class);
    }

    public function test_cria_requisicao_com_sucesso(): void
    {
        $user = $this->createCitizen();
        $book = $this->createBook();

        $this->actingAs($user);

        $requisition = $this->service->createRequisition($user, $book->id);

        $this->assertInstanceOf(Requisition::class, $requisition);
        $this->assertDatabaseHas('requisitions', [
            'id' => $requisition->id,
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => Requisition::STATUS_ACTIVE,
        ]);
    }

    public function test_impede_requisicao_se_livro_indisponivel(): void
    {
        $owner = $this->createCitizen();
        $book = $this->createBook();

        Requisition::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
        ]);

        $otherUser = $this->createCitizen();
        $this->actingAs($otherUser);

        $countBefore = Requisition::count();

        $this->expectException(BookUnavailableException::class);
        $this->expectExceptionMessage('Book is not available.');

        try {
            $this->service->createRequisition($otherUser, $book->id);
        } finally {
            $this->assertDatabaseCount('requisitions', $countBefore);
        }
    }

    public function test_impede_requisicao_quando_utilizador_ja_tem_tres_ativas(): void
    {
        $user = $this->createCitizen();

        for ($index = 0; $index < 3; $index++) {
            Requisition::create([
                'user_id' => $user->id,
                'book_id' => $this->createBook()->id,
            ]);
        }

        $countBefore = Requisition::count();
        $this->actingAs($user);

        $this->expectException(UserRequisitionLimitExceededException::class);
        $this->expectExceptionMessage('You already have 3 active requisitions.');

        try {
            $this->service->createRequisition($user, $this->createBook()->id);
        } finally {
            $this->assertDatabaseCount('requisitions', $countBefore);
        }
    }

    public function test_lanca_excecao_correta_para_livro_inexistente(): void
    {
        $user = $this->createCitizen();
        $this->actingAs($user);

        $countBefore = Requisition::count();

        $this->expectException(BookUnavailableException::class);
        $this->expectExceptionMessage('Book not found.');

        try {
            $this->service->createRequisition($user, 999999);
        } finally {
            $this->assertDatabaseCount('requisitions', $countBefore);
        }
    }

    private function createCitizen(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        return $user;
    }

    private function createBook(array $overrides = []): Book
    {
        $publisher = Publisher::create(['name' => 'Publisher '.uniqid()]);

        return Book::create(array_merge([
            'name' => 'Book '.uniqid(),
            'isbn' => '978-0-00-000000-'.uniqid(),
            'price' => 12.50,
            'publisher_id' => $publisher->id,
            'stock' => 5,
        ], $overrides));
    }
}
