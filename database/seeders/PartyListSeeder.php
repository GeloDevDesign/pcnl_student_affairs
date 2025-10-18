<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PartyList;

class PartyListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PartyList::create([
            'user_id' => 1,
            'name' => 'PARTIDO ISKOLAR',
            'slogan' => 'Official student party list for the SSC elections',
        ]);
    }
}
