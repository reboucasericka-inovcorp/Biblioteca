<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de acesso à Google Books API.
 *
 * Contrato: nunca acede à base de dados.
 * Sempre devolve arrays normalizados com estrutura fixa:
 * [google_volume_id, title, authors, publisher, published_date, description, isbn_13, thumbnail_url]
 */
class GoogleBooksService
{
    private const BASE_URL = 'https://www.googleapis.com/books/v1/volumes';

    private readonly ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.google_books.api_key');
    }

    /**
     * Pesquisa livros na Google Books API.
     *
     * @return array<int, array> Lista de volumes normalizados
     */
    public function search(string $query, int $maxResults = 20): array
    {
        if (empty(trim($query))) {
            return [];
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get(self::BASE_URL, $this->buildSearchParams($query, $maxResults));

            if (!$response->successful()) {
                Log::warning('Google Books API error', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            $data = $response->json();
            $items = $data['items'] ?? [];

            return array_map($this->normalizeVolume(...), $items);
        } catch (\Throwable $e) {
            Log::error('Google Books API exception', [
                'message' => $e->getMessage(),
                'query' => $query,
            ]);
            return [];
        }
    }

    /**
     * Obtém um volume por ID via endpoint direto.
     *
     * @return array|null Volume normalizado ou null se não encontrado/erro
     */
    public function getByVolumeId(string $volumeId): ?array
    {
        if (empty(trim($volumeId))) {
            return null;
        }

        try {
            $params = $this->apiKey ? ['key' => $this->apiKey] : [];

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get(self::BASE_URL . '/' . $volumeId, $params);

            if (!$response->successful()) {
                Log::warning('Google Books API getByVolumeId error', [
                    'status' => $response->status(),
                    'id' => $volumeId,
                ]);
                return null;
            }

            return $this->normalizeVolume($response->json());
        } catch (\Throwable $e) {
            Log::error('Google Books API getByVolumeId exception', [
                'message' => $e->getMessage(),
                'id' => $volumeId,
            ]);
            return null;
        }
    }

    private function buildSearchParams(string $query, int $maxResults): array
    {
        $params = [
            'q' => $query,
            'maxResults' => min($maxResults, 40),
        ];

        if ($this->apiKey) {
            $params['key'] = $this->apiKey;
        }

        return $params;
    }

    /**
     * @return array Estrutura fixa do contrato
     */
    private function normalizeVolume(array $item): array
    {
        $volumeInfo = $item['volumeInfo'] ?? [];
        $id = $item['id'] ?? null;

        return [
            'google_volume_id' => $id,
            'title' => $volumeInfo['title'] ?? null,
            'authors' => $volumeInfo['authors'] ?? [],
            'publisher' => $volumeInfo['publisher'] ?? null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'isbn_13' => $this->extractIsbn13($volumeInfo),
            'thumbnail_url' => $this->extractThumbnail($volumeInfo),
        ];
    }

    private function extractIsbn13(array $volumeInfo): ?string
    {
        $identifiers = $volumeInfo['industryIdentifiers'] ?? [];
        foreach ($identifiers as $id) {
            if (($id['type'] ?? '') === 'ISBN_13') {
                return $id['identifier'] ?? null;
            }
        }
        return null;
    }

    private function extractThumbnail(array $volumeInfo): ?string
    {
        $links = $volumeInfo['imageLinks'] ?? [];
        return $links['thumbnail'] ?? $links['smallThumbnail'] ?? null;
    }
}
