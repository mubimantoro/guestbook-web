<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'nama_lengkap' => 'Administrator',
            'nomor_hp' => '081234567898',
            'email' => 'adminkgtk@gmail.com',
            'username' => 'adminkgtk',
            'password' => bcrypt('password'),
        ]);

        $role = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $permissions = Permission::where('guard_name', 'api')->get();
        $role->syncPermissions($permissions);
        $user->assignRole($role);
    }
}
