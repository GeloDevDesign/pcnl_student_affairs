<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Election;

class ElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Election::create([
            'user_id' => '1',
            'name' => 'SSC VOTING',
            'start' => now(),
            'end' => now()->addDays(7),
            'status' => 1,
        ]);
    }
}
