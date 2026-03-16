<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureRequestContext
{
    /**
     * Mapear rotas para módulos
     * Usado para inferir o módulo correto a partir da rota
     */
    private const MODULE_MAP = [
        'books' => 'Book',
        'authors' => 'Author',
        'publishers' => 'Publisher',
        'requisitions' => 'Requisition',
        'reviews' => 'Review',
        'orders' => 'Order',
        'users' => 'User',
        'api/requisitions' => 'Requisition',
    ];

    /**
     * Processa o request e captura contexto
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Guardar informações do request no container para acesso posterior
        $request->attributes->set('request_ip', $request->ip());
        $request->attributes->set('request_user_agent', $request->userAgent());
        $request->attributes->set('request_module', $this->inferModule($request));

        return $next($request);
    }

    /**
     * Inferir o módulo a partir da rota
     */
    private function inferModule(Request $request): ?string
    {
        $path = trim($request->path(), '/');

        // Tentar match direto contra o mapa
        foreach (self::MODULE_MAP as $route => $module) {
            if (str_starts_with($path, $route)) {
                return $module;
            }
        }

        // Extrair primeira parte da rota como módulo (ex: /api/books/... -> 'books')
        $parts = explode('/', $path);
        if (! empty($parts)) {
            $firstPart = $parts[0];
            return $this->normalizeModuleName($firstPart);
        }

        return null;
    }

    /**
     * Normalizar nome do módulo (remover plural, etc)
     */
    private function normalizeModuleName(string $name): ?string
    {
        // Remover 'api' no início
        if ($name === 'api') {
            return null;
        }

        // Capitalizar primeira letra
        return ucfirst($name);
    }
}
