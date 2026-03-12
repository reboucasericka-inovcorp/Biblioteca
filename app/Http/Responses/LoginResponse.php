<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        // Redirecionar para o destino pretendido (ex.: /checkout) se existir
        $intended = $request->session()->pull('url.intended');
        if ($intended && \Illuminate\Support\Facades\URL::isValidUrl($intended)) {
            return redirect($intended);
        }
        $redirectParam = $request->query('redirect');
        if ($redirectParam && is_string($redirectParam) && str_starts_with($redirectParam, '/')) {
            return redirect($redirectParam);
        }

        $user = $request->user();
        if ($user && $user->hasRole('Admin')) {
            return redirect('/dashboard/admin');
        }

        return redirect('/dashboard/citizen');
    }
}
