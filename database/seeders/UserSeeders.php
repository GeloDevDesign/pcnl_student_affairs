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
                'first_name' => 'admin',
                'last_name' => 'admin',
                'id_number' => '2025-0002',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('Password123'),
                'role' => 'admin',
            ],
            [
                'first_name' => 'student1',
                'last_name' => 'student1',
                'id_number' => '2025-0001',
                'email' => 'student1@gmail.com',
                'password' => Hash::make('Password123'),
                'role' => 'student',
            ],


            [
                'first_name' => 'student2',
                'last_name' => 'student2',
                'id_number' => '2025-0001',
                'email' => 'student2@gmail.com',
                'password' => Hash::make('Password123'),
                'role' => 'student',
            ]
        ]);
    }
}
