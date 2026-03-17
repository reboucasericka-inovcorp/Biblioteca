<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /** Nomes de roles com guard fixo para consistência em dev/test/prod. */
    private const ROLES = [
        'Admin',
        'Cidadao',
    ];

    private const GUARD = 'web';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $name) {
            Role::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => self::GUARD,
                ]
            );
        }
    }
}
