<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $all = ['manage users', 'manage subjects', 'manage materials', 'manage questions', 'manage tryouts', 'view all results', 'use ai', 'view materials', 'take tryouts', 'view own results'];
        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        Role::firstOrCreate(['name' => 'admin'])->syncPermissions($all);
        Role::firstOrCreate(['name' => 'student'])->syncPermissions(['view materials', 'take tryouts', 'view own results']);
    }
}
