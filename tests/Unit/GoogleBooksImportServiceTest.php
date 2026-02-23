<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use App\Services\GoogleBooksImportService;
use App\Services\GoogleBooksService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleBooksImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_cria_book_authors_e_publisher(): void
    {
        $volume = [
            'google_volume_id' => 'xyz789',
            'title' => 'Design Patterns',
            'authors' => ['Erich Gamma', 'Richard Helm'],
            'publisher' => 'Addison-Wesley',
            'published_date' => '1994',
            'description' => 'Elements of Reusable Object-Oriented Software',
            'isbn_13' => '9780201633610',
            'thumbnail_url' => 'https://example.com/design-patterns.jpg',
        ];

        $importService = new GoogleBooksImportService(new GoogleBooksService());
        $book = $importService->import($volume);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertSame('Design Patterns', $book->name);
        $this->assertSame('xyz789', $book->google_volume_id);
        $this->assertSame('9780201633610', $book->isbn_13);

        $publisher = Publisher::where('name', 'Addison-Wesley')->first();
        $this->assertNotNull($publisher);
        $this->assertSame($publisher->id, $book->publisher_id);

        $authorNames = $book->authors->pluck('name')->toArray();
        $this->assertContains('Erich Gamma', $authorNames);
        $this->assertContains('Richard Helm', $authorNames);
    }

    public function test_import_by_volume_id_com_force_update_atualiza_campos_permitidos(): void
    {
        $publisher = Publisher::create(['name' => 'Old Publisher']);
        $book = Book::create([
            'google_volume_id' => 'vol123',
            'name' => 'Original Title',
            'isbn' => 'GB-vol123',
            'bibliography' => 'Original desc',
            'price' => 0,
            'publisher_id' => $publisher->id,
        ]);

        $volume = [
            'google_volume_id' => 'vol123',
            'title' => 'Updated Title',
            'authors' => ['New Author'],
            'publisher' => 'New Publisher',
            'published_date' => '2020',
            'description' => 'Updated description',
            'isbn_13' => '9781234567890',
            'thumbnail_url' => 'https://example.com/new-thumb.jpg',
        ];

        $mockService = $this->createMock(GoogleBooksService::class);
        $mockService->method('getByVolumeId')->willReturn($volume);

        $importService = new GoogleBooksImportService($mockService);
        $updated = $importService->importByVolumeId('vol123', true);

        $this->assertNotNull($updated);
        $this->assertSame($book->id, $updated->id);
        $this->assertSame('Updated Title', $updated->fresh()->name);
        $this->assertNotNull($updated->fresh()->bibliography);
        $this->assertSame('2020', $updated->fresh()->published_date);
        $this->assertSame('https://example.com/new-thumb.jpg', $updated->fresh()->thumbnail_url);

        $newPublisher = Publisher::where('name', 'New Publisher')->first();
        $this->assertNotNull($newPublisher);
        $this->assertSame($newPublisher->id, $updated->fresh()->publisher_id);
    }
}
