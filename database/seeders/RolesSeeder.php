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

    /**
     * Garante que as roles base existem (idempotente).
     * Usado pelo seeder e pelo registo de utilizadores.
     */
    public static function ensureBaseRolesExist(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (self::ROLES as $name) {
            Role::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => self::GUARD,
                ]
            );
        }

        $registrar->forgetCachedPermissions();
    }

    public function run(): void
    {
        self::ensureBaseRolesExist();
    }
}
