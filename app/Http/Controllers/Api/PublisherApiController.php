<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Publisher;

class PublisherApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Publisher::query();

        // 🔍 Search
        if ($s = $request->get('search')) {
            $query->where('name', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
        }

        // 🧭 Sorting
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');

        $query->orderBy($sort, $dir);

        $paginator = $query->paginate(10);
        $paginator->getCollection()->transform(function ($publisher) {
            $publisher->logo_url = $publisher->logo ? asset('storage/' . $publisher->logo) : null;
            return $publisher;
        });

        return ApiResponse::success($paginator);
    }
}