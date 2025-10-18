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
        $departments = ['BSA', 'BSBA', 'BSCRIM', 'BSIT', 'BSCE', 'BEE'];

        $users = [
            // ✅ Admin account
            [
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'id_number'  => 'ADMIN-0001',
                'email'      => 'admin@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'admin',
                'department' => null,
            ],
        ];

        // ✅ Generate 14 student accounts with random departments
        for ($i = 1; $i <= 14; $i++) {
            $users[] = [
                'first_name' => 'Student' . $i,
                'last_name'  => 'User' . $i,
                'id_number'  => '2025-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'email'      => 'student' . $i . '@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'student',
                'department' => $departments[array_rand($departments)],
            ];
        }

        // ✅ Insert all users at once
        User::insert($users);
    }
}
