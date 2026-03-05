<?php

namespace Tests\Feature;

use App\Mail\ReviewCreatedForAdmin;
use App\Mail\ReviewStatusUpdatedForCitizen;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\Requisition;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Cidadao']);
    }

    public function test_cidadao_nao_pode_avaliar_antes_de_devolver(): void
    {
        $citizen = $this->createCitizen();
        $book = $this->createBook();
        $requisition = $this->createRequisition($citizen, $book);

        Sanctum::actingAs($citizen);

        $response = $this->postJson("/api/requisitions/{$requisition->id}/review", [
            'rating' => 4,
            'comment' => 'Gostei muito do conteúdo.',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Review is allowed only after the requisition is returned.']);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_cidadao_cria_review_suspended_e_envia_email_para_admin(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $citizen = $this->createCitizen();
        $book = $this->createBook();
        $requisition = $this->createReturnedRequisition($citizen, $book);

        Sanctum::actingAs($citizen);

        $response = $this->postJson("/api/requisitions/{$requisition->id}/review", [
            'rating' => 5,
            'comment' => 'Excelente livro para aprofundar arquitetura limpa.',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Review created and awaiting moderation.']);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $citizen->id,
            'book_id' => $book->id,
            'requisition_id' => $requisition->id,
            'status' => Review::STATUS_SUSPENDED,
        ]);

        Mail::assertSent(ReviewCreatedForAdmin::class, function (ReviewCreatedForAdmin $mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
    }

    public function test_admin_aprova_review_e_notifica_cidadao(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $citizen = $this->createCitizen();
        $book = $this->createBook();
        $requisition = $this->createReturnedRequisition($citizen, $book);
        $review = Review::create([
            'user_id' => $citizen->id,
            'book_id' => $book->id,
            'requisition_id' => $requisition->id,
            'rating' => 4,
            'comment' => 'Bom conteúdo técnico.',
            'status' => Review::STATUS_SUSPENDED,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/reviews/{$review->id}/approve");

        $response->assertOk();
        $response->assertJson(['message' => 'Review approved successfully.']);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_ACTIVE,
            'refusal_reason' => null,
        ]);

        Mail::assertSent(ReviewStatusUpdatedForCitizen::class, function (ReviewStatusUpdatedForCitizen $mail) use ($citizen) {
            return $mail->hasTo($citizen->email) && $mail->review->status === Review::STATUS_ACTIVE;
        });
    }

    public function test_admin_recusa_review_com_justificativa_e_notifica_cidadao(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $citizen = $this->createCitizen();
        $book = $this->createBook();
        $requisition = $this->createReturnedRequisition($citizen, $book);
        $review = Review::create([
            'user_id' => $citizen->id,
            'book_id' => $book->id,
            'requisition_id' => $requisition->id,
            'rating' => 2,
            'comment' => 'Precisa de mais exemplos práticos.',
            'status' => Review::STATUS_SUSPENDED,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/reviews/{$review->id}/reject", [
            'reason' => 'Comentário com linguagem inadequada.',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Review rejected successfully.']);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_REFUSED,
            'refusal_reason' => 'Comentário com linguagem inadequada.',
        ]);

        Mail::assertSent(ReviewStatusUpdatedForCitizen::class, function (ReviewStatusUpdatedForCitizen $mail) use ($citizen) {
            return $mail->hasTo($citizen->email) && $mail->review->status === Review::STATUS_REFUSED;
        });
    }

    public function test_apenas_reviews_ativas_aparecem_no_detalhe_do_livro(): void
    {
        $citizen = $this->createCitizen();
        $book = $this->createBook();
        $returnedOne = $this->createReturnedRequisition($citizen, $book);
        $returnedTwo = $this->createReturnedRequisition($citizen, $book);

        Review::create([
            'user_id' => $citizen->id,
            'book_id' => $book->id,
            'requisition_id' => $returnedOne->id,
            'rating' => 5,
            'comment' => 'Review ativa.',
            'status' => Review::STATUS_ACTIVE,
        ]);

        Review::create([
            'user_id' => $citizen->id,
            'book_id' => $book->id,
            'requisition_id' => $returnedTwo->id,
            'rating' => 1,
            'comment' => 'Review não aprovada.',
            'status' => Review::STATUS_SUSPENDED,
        ]);

        Sanctum::actingAs($citizen);

        $response = $this->getJson("/api/books/{$book->id}");
        $response->assertOk();
        $response->assertJsonCount(1, 'data.reviews');
        $response->assertJsonFragment([
            'comment' => 'Review ativa.',
            'status' => Review::STATUS_ACTIVE,
        ]);
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    private function createCitizen(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Cidadao');

        return $user;
    }

    private function createBook(): Book
    {
        $publisher = Publisher::create(['name' => 'Publisher '.uniqid()]);

        return Book::create([
            'name' => 'Book '.uniqid(),
            'isbn' => '978-1-00-000000-'.uniqid(),
            'price' => 10.00,
            'publisher_id' => $publisher->id,
        ]);
    }

    private function createRequisition(User $user, Book $book): Requisition
    {
        return Requisition::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    private function createReturnedRequisition(User $user, Book $book): Requisition
    {
        $requisition = $this->createRequisition($user, $book);
        $requisition->update([
            'status' => Requisition::STATUS_RETURNED,
            'return_date' => now(),
        ]);

        return $requisition->fresh();
    }
}
