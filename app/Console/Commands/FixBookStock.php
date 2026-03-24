<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\StockAdjustment;
use App\Models\StockReconciliationRun;
use App\Services\LogService;
use App\Services\StockReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reconciliação pontual para bases onde requisições active/late existiam sem
 * decremento de stock na aprovação (dados legados).
 *
 * Livros com stock_reconciled=true são ignorados (regra atual já aplicada).
 * Use --include-reconciled para forçar reanálise (uso avançado).
 */
class FixBookStock extends Command
{
    protected $signature = 'app:fix-book-stock
                            {--dry-run : Mostrar alterações sem gravar na base de dados}
                            {--force : Executar sem pedir confirmação}
                            {--include-reconciled : Incluir livros já marcados como reconciliados}';

    protected $description = 'Corrige stock dos livros face a requisições active/late (legado sem decremento na aprovação)';

    public function handle(StockReconciliationService $service): int
    {
        if (! Schema::hasTable('stock_reconciliation_runs')) {
            $this->error('Execute as migrações: php artisan migrate');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $onlyUnreconciled = ! (bool) $this->option('include-reconciled');

        $plan = $service->buildReconciliationPlan($onlyUnreconciled);
        $rows = $plan['apply'];
        $suspicious = $plan['suspicious'];

        foreach ($suspicious as $s) {
            $this->warn("Livro #{$s['id']} ({$s['name']}): stock ({$s['stock']}) < empréstimos active+late ({$s['out']}) — ignorado.");
        }

        if ($plan['skipped_reconciled'] > 0) {
            $this->line("Ignorados {$plan['skipped_reconciled']} livro(s) já com stock_reconciled.");
        }

        if ($rows === []) {
            $this->info('Nenhum livro precisa de ajuste.');

            return self::SUCCESS;
        }

        $this->table(
            ['book_id', 'nome (60 char)', 'em active+late', 'stock antes', 'stock depois'],
            array_map(
                fn (array $r) => [$r['id'], $r['name'], $r['out'], $r['antes'], $r['depois']],
                $rows
            )
        );

        if ($dryRun) {
            $this->warn('Modo --dry-run: nada foi gravado.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            if (! $this->confirm('Gravar estes valores de stock na base de dados?', false)) {
                $this->info('Cancelado.');

                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($rows) {
            $run = StockReconciliationRun::query()->create([]);

            foreach ($rows as $r) {
                StockAdjustment::query()->create([
                    'stock_reconciliation_run_id' => $run->id,
                    'book_id' => $r['id'],
                    'stock_before' => $r['antes'],
                    'stock_after' => $r['depois'],
                ]);

                Book::query()->whereKey($r['id'])->update([
                    'stock' => $r['depois'],
                    'stock_reconciled' => true,
                ]);

                LogService::record(
                    module: 'stock',
                    action: 'reconciliation',
                    objectId: $r['id'],
                    description: "Stock ajustado de {$r['antes']} para {$r['depois']}"
                );
            }

            LogService::record(
                module: 'stock',
                action: 'reconciliation',
                objectId: null,
                description: 'Reconciliação de stock executada ('.count($rows).' livros)'
            );
        });

        $this->info('Stock atualizado para '.count($rows).' livro(s).');

        return self::SUCCESS;
    }
}
