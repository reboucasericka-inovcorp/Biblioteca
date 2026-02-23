<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Requisition;
use App\Models\User;
use App\Mail\RequisitionCreated;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RequisitionController extends Controller
{
    public function index()
    {
        return view('requisitions.index');
    }

    public function store(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:books,id']);

        $user = Auth::user();
        $book = Book::findOrFail($request->book_id);

        // 🔒 REGRA 1 — Livro já está requisitado?
        $alreadyRequested = Requisition::where('book_id', $book->id)
            ->where('status', Requisition::STATUS_ACTIVE)
            ->exists();

        if ($alreadyRequested) {
            return ApiResponse::error('Book is not available.', 422);
        }

        // 🔒 REGRA 2 — Utilizador já tem 3 livros ativos?
        $activeCount = Requisition::where('user_id', $user->id)
            ->where('status', Requisition::STATUS_ACTIVE)
            ->count();

        if ($activeCount >= 3) {
            return ApiResponse::error('You already have 3 active requisitions.', 422);
        }

        $requisition = DB::transaction(fn () => Requisition::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'photo_path' => $user->profile_photo_path,
        ]));

        $requisition->load(['book', 'user']);
        Mail::to($user->email)->send(new RequisitionCreated($requisition));
        Mail::to(User::role('Admin')->get())->send(new RequisitionCreated($requisition));

        return ApiResponse::success(null, 'Requisition created successfully.', 201);
    }

    public function confirmReturn(Requisition $requisition)
    {
        if (!in_array($requisition->status, [
            Requisition::STATUS_ACTIVE,
            Requisition::STATUS_LATE
        ])) {
            return ApiResponse::error('Already returned', 422);
        }

        $returnDate = now();

        $requisition->update([
            'return_date' => $returnDate,
            'days_elapsed' => $returnDate->diffInDays($requisition->request_date),
            'status' => Requisition::STATUS_RETURNED,
        ]);

        return ApiResponse::success(null, 'Return confirmed');
    }
}
