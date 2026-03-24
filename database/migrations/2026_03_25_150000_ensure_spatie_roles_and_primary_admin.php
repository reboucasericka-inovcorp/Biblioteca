<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Migração reservada (já aplicada em alguns ambientes).
 * Roles e utilizador admin: aplicar via `php artisan db:seed` (RolesSeeder + DatabaseSeeder).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sem alterações de esquema — dados em seeders.
    }

    public function down(): void
    {
        //
    }
};
