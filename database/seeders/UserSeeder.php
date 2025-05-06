<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '+79991112233',
            'password' => bcrypt('password'), // простой пароль
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'phone' => '+79994445566',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}
