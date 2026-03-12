<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
     * Formulário para criar utilizador (incluindo Administrador).
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Guardar novo utilizador com role (Admin ou Cidadão).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|string|in:Admin,Cidadao',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('flash.banner', 'Utilizador criado com sucesso.')
            ->with('flash.bannerStyle', 'success');
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

    /**
     * Formulário para editar utilizador (nome, email, password opcional, role).
     */
    public function edit(User $user)
    {
        $user->load('roles');
        return view('users.edit', compact('user'));
    }

    /**
     * Atualizar utilizador.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:Admin,Cidadao',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::defaults()];
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('flash.banner', 'Utilizador atualizado com sucesso.')
            ->with('flash.bannerStyle', 'success');
    }

    /**
     * Eliminar utilizador (não permitir eliminar o próprio utilizador).
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return redirect()->back()
                ->with('flash.banner', 'Não pode eliminar a sua própria conta.')
                ->with('flash.bannerStyle', 'danger');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('flash.banner', 'Utilizador eliminado com sucesso.')
            ->with('flash.bannerStyle', 'success');
    }
}
