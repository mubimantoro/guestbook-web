<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Administrator',
            'email' => 'adminkgtk@gmail.com',
            'username' => 'adminkgtk',
            'password' => bcrypt('password'),
        ]);

        $role = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $user->assignRole($role);
    }
}
