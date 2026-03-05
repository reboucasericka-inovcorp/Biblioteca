<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    /**
     * Mostra a gestão de utilizadores (apenas Admin).
     */
    public function index()
    {
        return view('users.index');
    }

    /**
     * Mostra o detalhe do utilizador com histórico de requisições (apenas Admin).
     */
    public function show(User $user)
    {
        $user->load(['requisitions' => function ($q) {
            $q->with(['book.publisher', 'book.authors'])
                ->orderByDesc('created_at');
        }]);

        return view('users.show', compact('user'));
    }
}
