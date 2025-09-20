<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'first_name' => 'test',
                'last_name' => 'test',
                'id_number' => '2025-0002',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('Password123'),
                'role' => 'admin',
            ],
            [
                'first_name' => 'test',
                'last_name' => 'test',
                'id_number' => '2025-0001',
                'email' => 'student@gmail.com',
                'password' => Hash::make('Password123'),
                'role' => 'student',
            ]
        ]);
    }
}
