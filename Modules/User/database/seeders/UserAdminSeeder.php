<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserAdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $userAdmin = User::query()->updateOrCreate(
            ['email' => 'tungocvan@gmail.com'],
            [
                'name' => 'Từ Ngọc Vân',
                'password' => bcrypt('123456'),
                'account_type' => 'system',
                'is_active' => true,
            ]
        );

        $role = Role::findByName('Super Admin', 'admin');

        $userAdmin->assignRole($role);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
