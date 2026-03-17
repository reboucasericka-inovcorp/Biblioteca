<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Objetivo: garantir que as colunas log_date e log_time existem na tabela logs,
     * sem causar erro em bases novas (onde a tabela já foi criada com essas colunas)
     * nem em bases antigas (onde a tabela existe mas ainda não tem essas colunas).
     */
    public function up(): void
    {
        if (! Schema::hasTable('logs')) {
            return;
        }

        Schema::table('logs', function (Blueprint $table) {
            // Em bases novas, estas colunas já existem (definidas na migration de criação).
            // Em bases antigas, adicionamos como nullable para não falhar em tabelas com dados.
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

