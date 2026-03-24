<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     * Admin: layout com sidebar e badge Admin Mode.
     * Cidadão: layout com navbar horizontal simples.
     */
    public function render(): View
    {
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('Admin') && ! $this->requestIsCitizenPanel()) {
            return view('layouts.admin');
        }

        return view('layouts.citizen');
    }

    /**
     * Rotas do painel cliente devem usar o layout citizen mesmo para utilizadores Admin
     * (ex.: pré-visualizar área do cidadão sem a sidebar de administrador).
     */
    private function requestIsCitizenPanel(): bool
    {
        return request()->routeIs('dashboard.citizen');
    }
}
