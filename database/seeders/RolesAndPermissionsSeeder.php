<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'admin';

        $permissions = $this->loadModulePermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guardName,
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => $guardName,
        ]);

        $superAdmin->syncPermissions(
            Permission::where('guard_name', $guardName)->get()
        );

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function loadModulePermissions(): array
    {
        $permissions = [];

        foreach (File::directories(base_path('Modules')) as $modulePath) {
            $moduleConfigFile = $modulePath . '/Config/module.php';

            if (! File::exists($moduleConfigFile)) {
                continue;
            }

            $moduleConfig = require $moduleConfigFile;

            if (! ($moduleConfig['enabled'] ?? true)) {
                continue;
            }

            $modulePermissions = $moduleConfig['permissions'] ?? [];

            if (! is_array($modulePermissions)) {
                continue;
            }

            $permissions = array_merge($permissions, $modulePermissions);
        }

        return collect($permissions)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
