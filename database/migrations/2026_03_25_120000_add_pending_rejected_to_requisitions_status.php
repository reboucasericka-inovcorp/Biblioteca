<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite os valores pending e rejected no status.
     * - MySQL: ENUM original → VARCHAR.
     * - SQLite: migração original criava CHECK apenas com active/returned/late; relaxa para string.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE requisitions MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'active'");
        }

        if ($driver === 'sqlite') {
            Schema::table('requisitions', function (Blueprint $table) {
                $table->string('status', 32)->default('active')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE requisitions MODIFY COLUMN status ENUM('active','returned','late') NOT NULL DEFAULT 'active'");
        }

        if ($driver === 'sqlite') {
            Schema::table('requisitions', function (Blueprint $table) {
                $table->enum('status', ['active', 'returned', 'late'])->default('active')->change();
            });
        }
    }
};
