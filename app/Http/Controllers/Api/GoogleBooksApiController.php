<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Requisition;
use App\Services\GoogleBooksImportService;
use App\Services\GoogleBooksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\BookResource;
use App\Support\ApiResponse;

/**
 * Orquestra chamadas aos serviços Google Books.
 * Não contém lógica pesada nem acede diretamente à base de dados.
 */
class GoogleBooksApiController extends Controller
{
    public function __construct(
        private readonly GoogleBooksService $googleBooksService,
        private readonly GoogleBooksImportService $importService
    ) {}

    /**
     * GET /api/google-books/search?q=&maxResults=&enrich_catalog=1
     * enrich_catalog=1: adiciona in_catalog e book_id a cada resultado (para cidadão)
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:500',
            'maxResults' => 'nullable|integer|min:1|max:40',
            'enrich_catalog' => 'nullable|boolean',
        ]);

        $query = $validated['q'] ?? '';
        $maxResults = $validated['maxResults'] ?? 20;
        $enrichCatalog = $validated['enrich_catalog'] ?? false;

        $results = $this->googleBooksService->search($query, $maxResults);

        if ($enrichCatalog && !empty($results)) {
            $volumeIds = array_column($results, 'google_volume_id');
            $catalogBooks = Book::whereIn('google_volume_id', $volumeIds)
                ->get()
                ->keyBy('google_volume_id');

            foreach ($results as &$v) {
                $book = $catalogBooks->get($v['google_volume_id'] ?? null);
                $v['in_catalog'] = (bool) $book;
                $v['book_id'] = $book?->id;
            }
        }

        return ApiResponse::success(
            $results,
            'Pesquisa realizada com sucesso.',
            200
        );
    }

    /**
     * POST /api/google-books/import
     * Body: { "google_volume_id": "..." } ou { "volume": { ... } }
     */
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'google_volume_id' => 'nullable|string|max:100',
            'volume' => 'nullable|array',
            'volume.google_volume_id' => 'required_with:volume|string',
            'volume.title' => 'required_with:volume|string',
        ], [
            'volume.google_volume_id.required_with' => 'O volume deve conter google_volume_id.',
            'volume.title.required_with' => 'O volume deve conter title.',
        ]);

        $googleVolumeId = $validated['google_volume_id'] ?? null;
        $volume = $validated['volume'] ?? null;

        if (!$googleVolumeId && !$volume) {
            return ApiResponse::error('É necessário fornecer google_volume_id ou volume.', 422);
        }

        $force = $request->boolean('force_update');

        $book = $googleVolumeId
            ? $this->importService->importByVolumeId($googleVolumeId, $force)
            : $this->importService->import($volume, $force);

        if (!$book) {
            return ApiResponse::error('Não foi possível importar o livro. Verifique se o volume existe e tente novamente.', 404);
        }

        $book->load(['publisher', 'authors'])
            ->loadCount(['requisitions as active_requisitions_count' => fn ($q) => $q->where('status', Requisition::STATUS_ACTIVE)]);
        return ApiResponse::success(
            new BookResource($book),
            'Livro importado com sucesso.',
            201
        );
    }
}
