<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\StockReconciliationRun;
use App\Services\LogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RollbackBookStock extends Command
{
    protected $signature = 'app:rollback-book-stock
                            {--dry-run : Mostrar o que seria revertido sem gravar}
                            {--force : Executar sem confirmação}';

    protected $description = 'Reverte a última execução de app:fix-book-stock (restaura stock_before)';

    public function handle(): int
    {
        if (! Schema::hasTable('stock_reconciliation_runs')) {
            $this->error('Execute as migrações: php artisan migrate');

            return self::FAILURE;
        }

        $run = StockReconciliationRun::query()
            ->whereNull('rolled_back_at')
            ->whereHas('adjustments')
            ->orderByDesc('id')
            ->first();

        if (! $run) {
            $this->warn('Nenhuma reconciliação ativa para reverter.');

            return self::SUCCESS;
        }

        $adjustments = $run->adjustments()->orderBy('id')->get();

        $this->table(
            ['book_id', 'stock_before (restaurar)', 'stock_after (atual gravado)'],
            $adjustments->map(fn ($a) => [$a->book_id, $a->stock_before, $a->stock_after])->all()
        );

        if ($this->option('dry-run')) {
            $this->warn('Modo --dry-run: nada foi gravado.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            if (! $this->confirm('Restaurar o stock anterior para estes livros?', false)) {
                $this->info('Cancelado.');

                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($run, $adjustments) {
            foreach ($adjustments as $adj) {
                Book::query()->whereKey($adj->book_id)->update([
                    'stock' => $adj->stock_before,
                    'stock_reconciled' => false,
                ]);

                LogService::record(
                    module: 'stock',
                    action: 'reconciliation_rollback',
                    objectId: $adj->book_id,
                    description: "Rollback de reconciliação: stock restaurado para {$adj->stock_before}"
                );
            }

            $run->update(['rolled_back_at' => now()]);

            LogService::record(
                module: 'stock',
                action: 'reconciliation_rollback',
                objectId: null,
                description: 'Rollback da última reconciliação de stock ('.$adjustments->count().' livros)'
            );
        });

        $this->info('Última reconciliação revertida.');

        return self::SUCCESS;
    }
}
