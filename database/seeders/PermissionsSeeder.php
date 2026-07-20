<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the permissions the application checks at runtime.
 *
 * Spatie throws PermissionDoesNotExist (it does not return false) when a
 * permission name is missing, so on a fresh database the admin sidebar — which
 * calls hasPermissionTo() — would fatal and take the dashboard down with it.
 * Seeding these rows makes the checks simply evaluate to false instead.
 *
 * Idempotent: safe to run on every deploy.
 */
class PermissionsSeeder extends Seeder
{
    /**
     * Keep in sync with the hasPermissionTo() calls in app/ and resources/views/.
     */
    public const PERMISSIONS = [
        'start stop zones',
        'manage cron zones',
        'manage setup',
        'shutdown restart',
        'manage users',
        'api log',
    ];

    public function run(): void
    {
        $guard = backpack_guard_name();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        // Spatie caches the permission table; drop it so the new rows are seen.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
