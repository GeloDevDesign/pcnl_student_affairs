<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'President',
            'Vice President',
            'Secretary',
            'Treasurer',
            'Auditor',
            'P.I.O',
        ];

        foreach ($roles as $role) {
            Role::create([
                'election_id' => 2, // assuming seeding for election 1
                'name' => $role,
            ]);
        }
    }
}
