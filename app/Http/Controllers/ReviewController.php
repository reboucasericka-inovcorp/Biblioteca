<?php

namespace App\Http\Controllers;

use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        return view('reviews.index');
    }

    public function show(Review $review)
    {
        $review->load(['user', 'book', 'requisition']);

        return view('reviews.show', compact('review'));
    }
}
