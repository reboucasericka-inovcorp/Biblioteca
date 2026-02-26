<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
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

        $paginator = $query->paginate(10);

        return ApiResponse::success($paginator);
    }
}