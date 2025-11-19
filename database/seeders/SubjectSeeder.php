<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Mathematics',
            'English',
            'Science',
            'History',
            'Programming',
            'Physics',
            'Chemistry',
        ];

        foreach ($subjects as $name) {
            Subject::create(['name' => $name]);
        }
    }
}
