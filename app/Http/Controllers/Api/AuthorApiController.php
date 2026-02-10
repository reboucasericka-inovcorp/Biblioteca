<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Author;

class AuthorApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Author::query();

        // 🔍 Search
        if ($s = $request->get('search')) {
            $query->where('name', 'like', "%{$s}%");
        }

        // 🧭 Sorting
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');

        $query->orderBy($sort, $dir);

        return $query->paginate(10);
    }
}