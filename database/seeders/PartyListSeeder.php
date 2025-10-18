<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartyList;

class PartyListSeeder extends Seeder
{
    public function run(): void
    {
        PartyList::create([
            'user_id' => 1,
            'election_id' => 1,
            'name' => 'PARTIDO ISKOLAR',
            'slogan' => 'Iskolar Para sa Bayan!',
        ]);

        PartyList::create([
            'user_id' => 1,
            'election_id' => 1,
            'name' => 'ISKO UNITY',
            'slogan' => 'Unity for Progress!',
        ]);
    }
}
