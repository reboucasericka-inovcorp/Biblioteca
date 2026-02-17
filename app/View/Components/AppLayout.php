<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

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

        if ($user instanceof User && $user->hasRole('Admin')) {
            return view('layouts.admin');
        }

        return view('layouts.citizen');
    }
}
