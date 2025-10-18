<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;
use App\Models\Role;
use App\Models\PartyList;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $electionId = 2; // Adjust if needed

        $partyLists = PartyList::where('election_id', $electionId)->get();
        $roles = Role::where('election_id', $electionId)->get();

        // Filipino-style names per role per party
        $candidateNames = [
            'President' => [
                'PARTIDO ISKOLAR' => 'Juan Dela Cruz',
                'ISKO UNITY' => 'Pedro Santos',
            ],
            'Vice President' => [
                'PARTIDO ISKOLAR' => 'Maria Reyes',
                'ISKO UNITY' => 'Josefina Cruz',
            ],
            'Secretary' => [
                'PARTIDO ISKOLAR' => 'Angela Ramos',
                'ISKO UNITY' => 'Carlo Jimenez',
            ],
            'Treasurer' => [
                'PARTIDO ISKOLAR' => 'Mark Salvador',
                'ISKO UNITY' => 'Angela Tan',
            ],
            'Auditor' => [
                'PARTIDO ISKOLAR' => 'Ricardo Mendoza',
                'ISKO UNITY' => 'Liza Villanueva',
            ],
            'P.I.O' => [
                'PARTIDO ISKOLAR' => 'Carlos Dizon',
                'ISKO UNITY' => 'Andrea Lim',
            ],
        ];

        foreach ($roles as $role) {
            foreach ($partyLists as $party) {
                $name = $candidateNames[$role->name][$party->name]
                    ?? $this->fallbackName($role->name, $party->name);

                Candidate::create([
                    'full_name' => $name,
                    'election_id' => $electionId,
                    'role_id' => $role->id,
                    'party_id' => $party->id,
                ]);
            }
        }
    }

    private function fallbackName($role, $party)
    {
        // If no name is defined in the $candidateNames array
        return "{$party} - {$role} Candidate";
    }
}
