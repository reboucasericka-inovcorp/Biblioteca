<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Exports\BooksExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BookApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['publisher', 'authors', 'requisitions']);

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

        $perPage = $request->get('per_page', 10);
        $paginator = $query->paginate((int) $perPage);
        $paginator->getCollection()->transform(function ($book) {
            $book->is_available = $book->isAvailable();
            $book->cover_url = $book->cover ? asset('storage/' . $book->cover) : null;
            return $book;
        });

        return $paginator;
    }

    public function show(Book $book)
    {
        $book->load(['publisher', 'authors', 'requisitions.user']);

        return response()->json([
            ...$book->toArray(),
            'is_available' => $book->isAvailable(),
            'cover_url' => $book->cover ? asset('storage/' . $book->cover) : null,
        ]);
    }

    public function export(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        return Excel::download(new BooksExport($search, $sort, $dir), 'books.xlsx');
    }
}