<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Idempotente: só adiciona log_date e log_time se ainda não existirem.
     * Evita "duplicate column name" quando a tabela logs já foi criada com esses campos
     * pela migration create_logs_table.
     */
    public function up(): void
    {
        if (! Schema::hasTable('logs')) {
            return;
        }

        Schema::table('logs', function (Blueprint $table) {
            if (! Schema::hasColumn('logs', 'log_date')) {
                $table->date('log_date')->nullable();
            }
            if (! Schema::hasColumn('logs', 'log_time')) {
                $table->time('log_time')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('logs')) {
            return;
        }

        Schema::table('logs', function (Blueprint $table) {
            if (Schema::hasColumn('logs', 'log_date')) {
                $table->dropColumn('log_date');
            }
            if (Schema::hasColumn('logs', 'log_time')) {
                $table->dropColumn('log_time');
            }
        });
    }
};
