<?php

namespace Tests\Feature;

use App\Mail\BookAvailableNotification;
use App\Models\Book;
use App\Models\BookAvailabilityAlert;
use App\Models\Publisher;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookAvailabilityAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_cidadao_pode_subscrever_alerta(): void
    {
        $citizen = $this->createCitizen();
        $holder = $this->createCitizen();
        $book = $this->createBook();
        $this->createActiveRequisition($holder, $book);

        Sanctum::actingAs($citizen);

        $response = $this->postJson("/api/books/{$book->id}/alerts");

        $response->assertOk();
        $response->assertJson(['message' => 'You will be notified when the book is available.']);
        $this->assertDatabaseHas('book_availability_alerts', [
            'user_id' => $citizen->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_nao_permite_duplicar_alerta(): void
    {
        $citizen = $this->createCitizen();
        $holder = $this->createCitizen();
        $book = $this->createBook();
        $this->createActiveRequisition($holder, $book);

        Sanctum::actingAs($citizen);

        $this->postJson("/api/books/{$book->id}/alerts")->assertOk();
        $this->postJson("/api/books/{$book->id}/alerts")->assertOk();

        $this->assertDatabaseCount('book_availability_alerts', 1);
    }

    public function test_alerta_so_pode_ser_criado_se_livro_estiver_indisponivel(): void
    {
        $citizen = $this->createCitizen();
        $book = $this->createBook();

        Sanctum::actingAs($citizen);

        $response = $this->postJson("/api/books/{$book->id}/alerts");

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Alerts are available only for unavailable books.']);
        $this->assertDatabaseCount('book_availability_alerts', 0);
    }

    public function test_devolucao_do_livro_envia_email_para_inscritos(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $holder = $this->createCitizen();
        $subscriber = $this->createCitizen();
        $book = $this->createBook();
        $requisition = $this->createActiveRequisition($holder, $book);

        Sanctum::actingAs($subscriber);
        $this->postJson("/api/books/{$book->id}/alerts")->assertOk();

        Sanctum::actingAs($admin);
        $this->postJson("/api/requisitions/{$requisition->id}/return")->assertOk();

        Mail::assertSent(BookAvailableNotification::class, function (BookAvailableNotification $mail) use ($subscriber) {
            return $mail->hasTo($subscriber->email);
        });
    }

    public function test_notified_at_e_preenchido_apos_envio(): void
    {
        Mail::fake();

        $admin = $this->createAdmin();
        $holder = $this->createCitizen();
        $subscriber = $this->createCitizen();
        $book = $this->createBook();
        $requisition = $this->createActiveRequisition($holder, $book);

        Sanctum::actingAs($subscriber);
        $this->postJson("/api/books/{$book->id}/alerts")->assertOk();

        $alert = BookAvailabilityAlert::first();
        $this->assertNull($alert?->notified_at);

        Sanctum::actingAs($admin);
        $this->postJson("/api/requisitions/{$requisition->id}/return")->assertOk();

        $alert->refresh();
        $this->assertNotNull($alert->notified_at);
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
        $publisher = Publisher::create([
            'name' => 'Publisher '.uniqid(),
        ]);

        return Book::create([
            'name' => 'Book '.uniqid(),
            'isbn' => '978-2-00-000000-'.uniqid(),
            'price' => 9.90,
            'publisher_id' => $publisher->id,
            'bibliography' => 'Livro para testes de alertas.',
        ]);
    }

    private function createActiveRequisition(User $user, Book $book): Requisition
    {
        return Requisition::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
