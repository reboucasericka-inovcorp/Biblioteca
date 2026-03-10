<?php

namespace App\Http\Controllers\Api;

use App\Exports\BooksExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\BookAvailabilityAlert;
use App\Models\Requisition;
use App\Models\Review;
use App\Services\RelatedBooksService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BookApiController extends Controller
{
    public function __construct(
        private readonly RelatedBooksService $relatedBooksService
    ) {}

    public function index(Request $request)
    {
        $query = Book::where('is_active', true)
            ->with(['publisher', 'authors'])
            ->withCount(['requisitions as active_requisitions_count' => fn ($q) => $q->whereIn('status', [
                Requisition::STATUS_ACTIVE,
                Requisition::STATUS_LATE,
            ])]);

        // 🔍 Search
        if ($s = $request->get('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('isbn', 'like', "%{$s}%")
                    ->orWhereHas('authors', fn ($aq) => $aq->where('name', 'like', "%{$s}%"));
            });
        }

        // 📂 Type (recent = últimos, tech = categoria tecnologia)
        $type = $request->get('type');
        if ($type === 'tech') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%tecnologia%')
                    ->orWhere('name', 'like', '%programação%')
                    ->orWhereHas('authors', fn ($aq) => $aq->where('name', 'like', '%tecnologia%'));
            });
        }

        // 🧭 Sorting (recent e featured: created_at desc por defeito)
        $sort = $request->get('sort', in_array($type, ['recent', 'featured']) ? 'created_at' : 'name');
        $dir = $request->get('dir', in_array($type, ['recent', 'featured']) ? 'desc' : 'asc');
        $query->orderBy($sort, $dir);

        // Home: lista simples não paginada (recent, tech, featured)
        if (in_array($type, ['recent', 'tech', 'featured'])) {
            $defaultLimit = $type === 'featured' ? 6 : ($type === 'recent' ? 30 : 6);
            $limit = min((int) $request->get('per_page', $defaultLimit), $type === 'recent' ? 30 : 20);
            $books = $query->limit($limit)->get();

            return ApiResponse::success(BookResource::collection($books));
        }

        // Listagem principal: paginada
        $perPage = $request->get('per_page', 10);
        $paginator = $query->paginate((int) $perPage);
        $paginator->through(fn ($book) => new BookResource($book));

        return ApiResponse::success($paginator);
    }

    public function show(Request $request, Book $book)
    {
        if (! $book->is_active) {
            abort(404);
        }

        $book->load([
            'publisher',
            'authors',
            'requisitions.user',
            'reviews' => function ($query) {
                $query->where('status', Review::STATUS_ACTIVE)
                    ->with('user')
                    ->latest();
            },
        ])
            ->loadCount(['requisitions as active_requisitions_count' => fn ($q) => $q->whereIn('status', [
                Requisition::STATUS_ACTIVE,
                Requisition::STATUS_LATE,
            ])]);

        $book->setAttribute(
            'related_books',
            $this->relatedBooksService->findRelatedBooks($book)
        );

        $user = $request->user();
        $book->setAttribute(
            'has_pending_availability_alert',
            false
        );
        $book->setAttribute(
            'has_subscribed_availability_alert',
            false
        );
        if ($user) {
            $alert = BookAvailabilityAlert::where('book_id', $book->id)
                ->where('user_id', $user->id)
                ->first(['id', 'notified_at']);

            $book->setAttribute('has_subscribed_availability_alert', (bool) $alert);
            $book->setAttribute('has_pending_availability_alert', (bool) ($alert && $alert->notified_at === null));
        }

        return ApiResponse::success(new BookResource($book));
    }

    public function export(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        return Excel::download(new BooksExport($search, $sort, $dir), 'books.xlsx');
    }
}
