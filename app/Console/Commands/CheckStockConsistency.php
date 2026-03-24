<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LogService;
use App\Services\StockReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckStockConsistency extends Command
{
    protected $signature = 'app:check-stock-consistency';

    protected $description = 'Deteta inconsistências de stock (stock < requisições active+late)';

    public function handle(StockReconciliationService $service): int
    {
        $list = $service->inconsistencies();

        if ($list === []) {
            $this->info('Sem inconsistências de stock.');

            return self::SUCCESS;
        }

        $n = count($list);
        $this->warn("Encontradas {$n} inconsistência(s).");

        $this->table(
            ['id', 'livro', 'stock', 'em empréstimo', 'diferença'],
            array_map(
                fn (array $r) => [$r['id'], mb_substr($r['title'], 0, 40), $r['stock'], $r['out_on_loan'], $r['diff']],
                $list
            )
        );

        LogService::record(
            module: 'stock',
            action: 'consistency_check',
            objectId: null,
            description: "Verificação de stock: {$n} inconsistência(s) detetada(s)"
        );

        if (filter_var(env('STOCK_INCONSISTENCY_MAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            $emails = User::role('Admin')->pluck('email')->filter()->unique()->values()->all();
            if ($emails !== []) {
                Mail::raw(
                    "Stock: {$n} livro(s) com stock inferior ao número de requisições active/late.\n\n".
                    'Comando: php artisan app:check-stock-consistency',
                    function ($message) use ($emails) {
                        $message->to($emails)->subject('[Biblioteca] Inconsistências de stock');
                    }
                );
                $this->info('Email enviado aos administradores.');
            }
        }

        return self::SUCCESS;
    }
}
