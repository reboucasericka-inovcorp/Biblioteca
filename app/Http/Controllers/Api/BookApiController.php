<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Requisition;
use App\Exports\BooksExport;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BookApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['publisher', 'authors'])
            ->withCount(['requisitions as active_requisitions_count' => fn ($q) => $q->where('status', Requisition::STATUS_ACTIVE)]);

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

        // 🧭 Sorting (recent usa created_at por defeito)
        $sort = $request->get('sort', $type === 'recent' ? 'created_at' : 'name');
        $dir  = $request->get('dir', $type === 'recent' ? 'desc' : 'asc');
        $query->orderBy($sort, $dir);

        // Home/recent: lista simples não paginada (até 8 itens)
        if (in_array($type, ['recent', 'tech'])) {
            $limit = min((int) $request->get('per_page', 8), 20);
            $books = $query->limit($limit)->get();
            return ApiResponse::success(BookResource::collection($books));
        }

        // Listagem principal: paginada
        $perPage = $request->get('per_page', 10);
        $paginator = $query->paginate((int) $perPage);
        $paginator->through(fn ($book) => new BookResource($book));

        return ApiResponse::success($paginator);
    }

    public function show(Book $book)
    {
        $book->load(['publisher', 'authors', 'requisitions.user'])
            ->loadCount(['requisitions as active_requisitions_count' => fn ($q) => $q->where('status', Requisition::STATUS_ACTIVE)]);

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