<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reconciliation_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_reconciliation_run_id')
                ->constrained('stock_reconciliation_runs')
                ->cascadeOnDelete();
            $table->foreignId('book_id')
                ->constrained('books')
                ->cascadeOnDelete();
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->timestamps();

            $table->index('book_id');
        });

        if (! Schema::hasColumn('books', 'stock_reconciled')) {
            Schema::table('books', function (Blueprint $table) {
                $table->boolean('stock_reconciled')->default(false)->after('stock');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_reconciliation_runs');

        if (Schema::hasColumn('books', 'stock_reconciled')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('stock_reconciled');
            });
        }
    }
};
