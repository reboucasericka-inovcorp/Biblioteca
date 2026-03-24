<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * `php artisan migrate` não cria roles — executar `php artisan db:seed` ou `php artisan migrate --seed`.
     * RolesSeeder corre primeiro para Admin/Cidadao existirem antes do utilizador admin.
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'reboucasericka@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('123456789'),
            ]
        );

        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
