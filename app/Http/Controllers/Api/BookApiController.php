<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookApiController extends Controller
{
    public function index (Request $request)
    {
         $query = Book::with(['publisher', 'authors']);

        // 🔍 Search
        if ($s = $request->get('search')) {
            $query->where('name', 'like', "%{$s}%")
                  ->orWhere('isbn', 'like', "%{$s}%");
        }

        // 🧭 Sorting
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');
        $query->orderBy($sort, $dir);

        return $query->paginate(10);
    }
}