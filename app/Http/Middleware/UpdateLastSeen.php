<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $isPresenceStatusRequest = $request->is('api/chat/presence/status');
        $requestedOffline = $isPresenceStatusRequest && $request->input('status') === 'offline';

        $response = $next($request);

        $user = $request->user();
        if ($user && ! $requestedOffline) {
            $user->forceFill([
                'last_seen_at' => now(),
                'status' => 'online',
            ])->saveQuietly();
        }

        return $response;
    }
}
