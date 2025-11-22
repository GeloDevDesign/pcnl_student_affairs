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
        $users = [
            // ✅ Admin account
            [
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'id_number'  => 'ADMIN0001',
                'email'      => 'admin@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'admin',
                'department' => null,
            ],

            // ✅ Students (all BSIT)
            [
                'first_name' => 'Daturyan',
                'last_name'  => 'James',
                'id_number'  => '20250001',
                'email'      => 'daturyanjames@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'student',
                'department' => 'BSIT',
            ],
            [
                'first_name' => 'Kim',
                'last_name'  => 'Saena',
                'id_number'  => '20250002',
                'email'      => 'kimsaena377@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'student',
                'department' => 'BSIT',
            ],
            [
                'first_name' => 'Jemar',
                'last_name'  => 'Langmalakas',
                'id_number'  => '20250003',
                'email'      => 'jemarlangmalakas@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'student',
                'department' => 'BSIT',
            ],

               [
                'first_name' => 'Angelo',
                'last_name'  => 'Serenuela',
                'id_number'  => '20250011',
                'email'      => 'angeloserenuela524@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'student',
                'department' => 'BSIT',
            ],
            [
                'first_name' => 'Jolina',
                'last_name'  => 'Mapalo',
                'id_number'  => '20250004',
                'email'      => 'jolinamapalo@gmail.com',
                'password'   => Hash::make('Password123'),
                'role'       => 'student',
                'department' => 'BSIT',
            ],
        ];

        User::insert($users);
    }
}
