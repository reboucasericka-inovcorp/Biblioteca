<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleBooksImportService
{
    public function __construct(
        private readonly GoogleBooksService $googleBooksService
    ) {}

    /**
     * Importa um volume por ID.
     */
    public function importByVolumeId(string $volumeId, bool $forceUpdate = false): ?Book
    {
        $existing = Book::where('google_volume_id', $volumeId)->first();

        if ($existing && !$forceUpdate) {
            return $existing;
        }

        $volume = $this->googleBooksService->getByVolumeId($volumeId);

        if (!$volume) {
            Log::warning('GoogleBooksImport: volume não encontrado na API', [
                'id' => $volumeId
            ]);
            return null;
        }

        return $this->import($volume, $forceUpdate);
    }

    /**
     * Importa a partir de array normalizado.
     */
    public function import(array $volume, bool $forceUpdate = false): ?Book
    {
        $googleVolumeId = $volume['google_volume_id'] ?? null;
        $title = $volume['title'] ?? null;

        if (!$googleVolumeId || !$title) {
            Log::warning('GoogleBooksImport: volume inválido', [
                'volume' => $volume
            ]);
            return null;
        }

        return DB::transaction(function () use ($volume, $forceUpdate) {

            $book = $this->findOrCreateBook($volume);
            if (!$book) {
                return null;
            }

            $this->updateBookAttributes($book, $volume, $forceUpdate);
            $book->save();

            $authorIds = $this->findOrCreateAuthors($volume['authors'] ?? []);
            $book->authors()->sync($authorIds);

            Log::info('Livro sincronizado com Google Books', [
                'book_id' => $book->id,
                'google_volume_id' => $book->google_volume_id,
                'force_update' => $forceUpdate,
            ]);

            return $book->fresh(['publisher', 'authors']);
        });
    }

    private function findOrCreateBook(array $volume): ?Book
    {
        $isbn13 = $volume['isbn_13'] ?? null;
        $googleVolumeId = $volume['google_volume_id'] ?? null;

        if ($isbn13) {
            $existing = Book::where('isbn_13', $isbn13)->first();
            if ($existing) return $existing;
        }

        if ($googleVolumeId) {
            $existing = Book::where('google_volume_id', $googleVolumeId)->first();
            if ($existing) return $existing;
        }

        $book = new Book();
        $book->google_volume_id = $googleVolumeId;
        $book->isbn_13 = $isbn13;
        $book->price = 0;
        $book->isbn = $isbn13 ?? ('GB-' . $googleVolumeId);

        return $book;
    }

    private function findOrCreatePublisher(?string $name): Publisher
    {
        $name = trim($name ?? '') ?: 'Desconhecido';

        return Publisher::firstOrCreate(
            ['name' => $name],
            ['name' => $name]
        );
    }

    private function findOrCreateAuthors(array $authors): array
    {
        $ids = [];

        foreach ($authors as $name) {
            $name = trim($name ?? '');
            if ($name === '') continue;

            $author = Author::firstOrCreate(
                ['name' => $name],
                ['name' => $name]
            );

            $ids[] = $author->id;
        }

        return $ids;
    }

    private function updateBookAttributes(Book $book, array $volume, bool $forceUpdate): void
    {
        $book->name = trim($volume['title'] ?? '') ?: 'Título indisponível no momento da importação.';

        if ($forceUpdate || empty($book->bibliography)) {
            $book->bibliography = trim($volume['description'] ?? '') ?: 'Descrição indisponível no momento da importação.';
        }

        $book->publisher_id = $this->findOrCreatePublisher($volume['publisher'] ?? null)->id;

        if ($forceUpdate || empty($book->published_date)) {
            $book->published_date = trim($volume['published_date'] ?? '') ?: null;
        }

        if ($forceUpdate || empty($book->thumbnail_url)) {
            $book->thumbnail_url = trim($volume['thumbnail_url'] ?? '') ?: null;
        }

        if (empty($book->isbn_13)) {
            $book->isbn_13 = trim($volume['isbn_13'] ?? '') ?: null;
        }

        if (empty($book->google_volume_id)) {
            $book->google_volume_id = trim($volume['google_volume_id'] ?? '') ?: null;
        }
    }
}