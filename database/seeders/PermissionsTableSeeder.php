<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'kategori_kunjungan', 'guard_name' => 'api']);
        Permission::create(['name' => 'staff', 'guard_name' => 'api']);
        Permission::create(['name' => 'absensi_staff', 'guard_name' => 'api']);
        Permission::create(['name' => 'tamu', 'guard_name' => 'api']);
        Permission::create(['name' => 'roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'users', 'guard_name' => 'api']);
        Permission::create(['name' => 'permissions', 'guard_name' => 'api']);
    }
}
