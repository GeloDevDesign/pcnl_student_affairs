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
        for ($i = 1; $i <= 10; $i++) {
            Election::create([
                'user_id' => 1,
                'name' => "SSC Election {$i}",
                'start_date' => Carbon::now()->subMonths($i)->startOfMonth(),
                'end_date' => Carbon::now()->subMonths($i)->endOfMonth(),
                'status' => $this->randomStatus(),
            ]);
        }
    }

    /**
     * Get a random election status (0 = Scheduled, 1 = Ongoing, 2 = Closed)
     */
    private function randomStatus(): int
    {
        return [0, 1, 2][array_rand([0, 1, 2])];
    }
}
