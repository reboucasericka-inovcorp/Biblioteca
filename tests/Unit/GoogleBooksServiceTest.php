<?php

namespace Tests\Unit;

use App\Services\GoogleBooksService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksServiceTest extends TestCase
{
    public function test_search_retorna_estrutura_normalizada(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'id' => 'abc123',
                        'volumeInfo' => [
                            'title' => 'Clean Code',
                            'authors' => ['Robert Martin'],
                            'publisher' => 'Prentice Hall',
                            'publishedDate' => '2008',
                            'description' => 'A handbook of agile software craftsmanship.',
                            'industryIdentifiers' => [
                                ['type' => 'ISBN_13', 'identifier' => '9780132350884'],
                            ],
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/thumb.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new GoogleBooksService();
        $results = $service->search('clean code', 5);

        $this->assertIsArray($results);
        $this->assertCount(1, $results);

        $volume = $results[0];
        $this->assertArrayHasKey('google_volume_id', $volume);
        $this->assertArrayHasKey('title', $volume);
        $this->assertArrayHasKey('authors', $volume);
        $this->assertArrayHasKey('publisher', $volume);
        $this->assertArrayHasKey('published_date', $volume);
        $this->assertArrayHasKey('description', $volume);
        $this->assertArrayHasKey('isbn_13', $volume);
        $this->assertArrayHasKey('thumbnail_url', $volume);

        $this->assertSame('abc123', $volume['google_volume_id']);
        $this->assertSame('Clean Code', $volume['title']);
        $this->assertSame(['Robert Martin'], $volume['authors']);
        $this->assertSame('Prentice Hall', $volume['publisher']);
        $this->assertSame('2008', $volume['published_date']);
        $this->assertSame('9780132350884', $volume['isbn_13']);
        $this->assertSame('https://example.com/thumb.jpg', $volume['thumbnail_url']);
    }
}
