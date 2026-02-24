<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookSuggestionResource;
use App\Models\Book;
use App\Models\BookSuggestion;
use App\Services\GoogleBooksImportService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookSuggestionApiController extends Controller
{
    public function __construct(
        private readonly GoogleBooksImportService $importService
    ) {}

    /**
     * POST /api/book-suggestions
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'google_volume_id' => 'required|string|max:100',
            'title' => 'required|string|max:500',
            'authors' => 'nullable|array',
            'authors.*' => 'string|max:255',
            'thumbnail_url' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        if (Book::where('google_volume_id', $validated['google_volume_id'])->exists()) {
            return ApiResponse::error('Este livro já existe no catálogo. Pode requisitá-lo.', 422);
        }

        $suggestion = BookSuggestion::create([
            'user_id' => $user->id,
            'google_volume_id' => $validated['google_volume_id'],
            'title' => $validated['title'],
            'authors' => $validated['authors'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'status' => BookSuggestion::STATUS_PENDING,
        ]);

        return ApiResponse::success(
            new BookSuggestionResource($suggestion),
            'Sugestão enviada com sucesso.',
            201
        );
    }

    /**
     * GET /api/book-suggestions
     */
    public function index(Request $request): JsonResponse
    {
        $query = BookSuggestion::with('user');

        if (!$request->user()->hasRole('Admin')) {
            $query->where('user_id', $request->user()->id);
        } elseif ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $query->orderByDesc('created_at');
        $suggestions = $query->paginate(15);

        return ApiResponse::success($suggestions->through(fn ($s) => new BookSuggestionResource($s)));
    }

    /**
     * PATCH /api/book-suggestions/{id}/approve
     * Apenas Admin.
     */
    public function approve(BookSuggestion $bookSuggestion): JsonResponse
    {
        if ($bookSuggestion->status !== BookSuggestion::STATUS_PENDING) {
            return ApiResponse::error('Esta sugestão já foi processada.', 422);
        }

        $book = DB::transaction(function () use ($bookSuggestion) {
            $book = $this->importService->importByVolumeId($bookSuggestion->google_volume_id);
            if ($book) {
                $bookSuggestion->update(['status' => BookSuggestion::STATUS_APPROVED]);
            }
            return $book;
        });

        if (!$book) {
            return ApiResponse::error('Não foi possível importar o livro. Verifique o volume na Google Books.', 500);
        }

        return ApiResponse::success(
            new BookSuggestionResource($bookSuggestion->fresh()),
            'Sugestão aprovada e livro importado com sucesso.'
        );
    }

    /**
     * PATCH /api/book-suggestions/{id}/reject
     */
    public function reject(BookSuggestion $bookSuggestion): JsonResponse
    {
        if ($bookSuggestion->status !== BookSuggestion::STATUS_PENDING) {
            return ApiResponse::error('Esta sugestão já foi processada.', 422);
        }

        $bookSuggestion->update(['status' => BookSuggestion::STATUS_REJECTED]);

        return ApiResponse::success(
            new BookSuggestionResource($bookSuggestion->fresh()),
            'Sugestão rejeitada.'
        );
    }
}
