<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        //

        User::insert(
            [
                'first_name' => 'test',
                'last_name' => 'test',
                'email' => 'test@email.com',
                'password' => Hash::make('Password123'),
                'role' => 'admin'
            ],
            [
                'first_name' => 'test',
                'last_name' => 'test',
                'email' => 'test2@email.com',
                'password' => Hash::make('Password123'),
                'role' => 'student'
            ]
        );
    }
}
