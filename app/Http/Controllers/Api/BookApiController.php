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

        // 🧭 Sorting
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');
        $query->orderBy($sort, $dir);

        $paginator = $query->paginate(10);
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