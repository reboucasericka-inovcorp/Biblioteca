<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LogService
{
    /**
     * Campos sensíveis que não devem ser logados ou mostrados
     * para proteger dados sensíveis na auditoria
     */
    private const SENSITIVE_FIELDS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'api_token',
        'api_secret',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'secret',
        'token',
    ];

    /** Campos que não devem ser considerados "alterados" no texto do log (ruído) */
    private const IGNORED_CHANGE_FIELDS = [
        'created_at',
        'updated_at',
    ];

    /** Tamanho máximo da coluna change (MySQL TEXT ≈ 64KB; deixar margem) */
    private const MAX_CHANGE_LENGTH = 65535;

    /**
     * Registar uma ação de log.
     *
     * Deve ser seguro em qualquer contexto (HTTP, CLI, Jobs, testes),
     * nunca lançando exceções para a operação principal.
     */
    public static function record(
        ?string $module,
        string $action,
        ?int $objectId = null,
        ?string $description = null,
        ?array $changes = null
    ): ?Log {
        $change = self::buildChangeDescription($action, $description, $changes);

        // Módulo: explícito > inferido do request > fallback para consistência (CLI/Jobs/analytics)
        $finalModule = $module ?? self::getModule() ?? 'System';

        // Em alguns contextos (ex.: SQLite :memory: antes do migrate, testes que removem a tabela),
        // a tabela pode não existir. O serviço de log deve ser sempre "best effort" e silencioso.
        if (! Schema::hasTable('logs')) {
            return null;
        }

        try {
            return Log::create([
                'log_date' => now()->toDateString(),
                'log_time' => now()->toTimeString(),
                'user_id' => self::getCurrentUserId(),
                'module' => $finalModule,
                'object_id' => $objectId,
                'change' => $change,
                'ip' => self::getRequestIp(),
                'browser' => self::getBrowser(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Registar uma ação de modelo (criação, atualização, exclusão)
     */
    public static function recordModel(
        Model $model,
        string $action,
        ?array $originalData = null
    ): ?Log {
        $module = class_basename($model);
        $description = self::getActionDescription($module, $action);
        $changes = null;

        if ($action === 'updated' && $originalData) {
            $changes = self::detectChanges($originalData, $model->getAttributes());
        }

        return self::record(
            module: $module,
            action: $action,
            objectId: $model->id,
            description: $description,
            changes: $changes
        );
    }

    /**
     * Construir descrição de mudança
     */
    private static function buildChangeDescription(
        string $action,
        ?string $description,
        ?array $changes
    ): string {
        $text = strip_tags((string) ($description ?? $action));

        if ($changes && ! empty($changes)) {
            $changedFields = array_keys($changes);
            $text .= ' (campos alterados: ' . implode(', ', $changedFields) . ')';
        }

        return mb_substr($text, 0, self::MAX_CHANGE_LENGTH);
    }

    /**
     * Obter descrição legível da ação.
     */
    private static function getActionDescription(string $module, string $action): string
    {
        $actions = [
            'created' => "criado",
            'updated' => "atualizado",
            'deleted' => "removido",
            'restored' => "restaurado",
            'returned' => "devolvido",
        ];

        $actionText = $actions[$action] ?? $action;

        return ucfirst($module) . " $actionText";
    }

    /**
     * Detectar quais campos foram alterados
     * Retorna array com campo => novo_valor (sem valores sensíveis)
     */
    private static function detectChanges(array $original, array $current): array
    {
        $changes = [];

        foreach ($current as $field => $value) {
            if (self::isSensitiveField($field) || self::isIgnoredChangeField($field)) {
                continue;
            }

            if (! isset($original[$field]) || $original[$field] != $value) {
                $changes[$field] = self::sanitizeValue($value);
            }
        }

        return $changes;
    }

    /**
     * Verificar se um campo é sensível.
     *
     * Critérios:
     * - match exato em SENSITIVE_FIELDS
     * - OU contém algum dos fragmentos: password, token, secret, recovery, two_factor
     */
    private static function isSensitiveField(string $field): bool
    {
        if (in_array($field, self::SENSITIVE_FIELDS)) {
            return true;
        }

        $lower = strtolower($field);
        $fragments = [
            'password',
            'token',
            'secret',
            'recovery',
            'two_factor',
        ];

        foreach ($fragments as $fragment) {
            if (str_contains($lower, $fragment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Campos que não devem aparecer como "alterados" no log (apenas ruído)
     */
    private static function isIgnoredChangeField(string $field): bool
    {
        return in_array($field, self::IGNORED_CHANGE_FIELDS);
    }

    /**
     * Sanitizar valor para log (remover dados sensíveis)
     */
    private static function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            // Se parece ser um hash/token longo, mostrar apenas número de caracteres
            if (strlen($value) > 50 && preg_match('/^[a-f0-9]+$|^[a-zA-Z0-9_\-\.]+$/', $value)) {
                return '[' . strlen($value) . ' chars]';
            }
        }

        return $value;
    }

    /**
     * Obter IP do request de forma segura (CLI, jobs, testes não têm request HTTP).
     *
     * Ordem de precedência:
     * - atributo "request_ip" capturado pelo middleware CaptureRequestContext
     * - request()->ip() padrão do Laravel
     * - null se não existir request (CLI, jobs, testes unitários)
     */
    private static function getRequestIp(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        if ($request->attributes->has('request_ip')) {
            return $request->attributes->get('request_ip');
        }

        return $request->ip();
    }

    /**
     * Obter navegador/user-agent legível.
     *
     * Ordem de precedência:
     * - atributo "request_user_agent" capturado pelo middleware CaptureRequestContext
     * - request()->userAgent()
     * - "Unknown" se não existir request ou User-Agent
     */
    private static function getBrowser(): string
    {
        if (! app()->bound('request')) {
            return 'Unknown';
        }

        $request = request();

        $userAgent = $request->attributes->get('request_user_agent')
            ?? $request->userAgent();

        if (! $userAgent) {
            return 'Unknown';
        }

        // Extrair informações básicas do User-Agent
        if (stripos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (stripos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } elseif (stripos($userAgent, 'Trident') !== false) {
            return 'IE';
        }

        // Se for API/bot, extrair tipo
        if (stripos($userAgent, 'curl') !== false) {
            return 'API (curl)';
        }

        return 'Other';
    }

    /**
     * Obter módulo inferido a partir do contexto do request (middleware).
     *
     * Em ambientes sem request (CLI, jobs, testes unitários), devolve null.
     */
    private static function getModule(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        if ($request->attributes->has('request_module')) {
            return $request->attributes->get('request_module');
        }

        return null;
    }

    /**
     * Obter ID do utilizador autenticado de forma segura (CLI/Jobs não têm request).
     */
    private static function getCurrentUserId(): ?int
    {
        if (! app()->bound('request')) {
            return null;
        }

        return auth()->id();
    }
}
