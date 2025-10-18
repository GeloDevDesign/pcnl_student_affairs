<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Election;
use Carbon\Carbon;

class ElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First Election: 2024
        Election::create([
            'user_id' => 1,
            'name' => '2024 PCNL Supreme Student Council Election',
            'start_date' => Carbon::create(2024, 4, 1),
            'end_date' => Carbon::create(2024, 4, 30),
            'status' => 2, // Closed
            'is_set' => false,
        ]);

        // Second Election: 2025
        Election::create([
            'user_id' => 1,
            'name' => '2025 PCNL Supreme Student Council Election',
            'start_date' => Carbon::create(2025, 4, 1),
            'end_date' => Carbon::create(2025, 4, 30),
            'status' => 1, // Ongoing
            'is_set' => true, // Make this the currently active one
        ]);
    }
}
