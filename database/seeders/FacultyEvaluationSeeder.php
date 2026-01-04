<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Instructor;
use App\Models\EvaluationCycle;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacultyEvaluationSeeder extends Seeder
{
    public function run()
    {
        // 1. FETCH Existing Students (IDs > 1)
        $students = User::where('id', '>', 1)->take(3)->get();

        // 2. Create Instructors
        $instructorsData = [
            ['name' => 'Mr. John Doe', 'department' => 4], 
            ['name' => 'Ms. Jane Smith', 'department' => 1], 
            ['name' => 'Dr. Albert Einstein', 'department' => 5], 
            ['name' => 'Mrs. Sarah Connor', 'department' => 3], 
            ['name' => 'Mr. Tony Stark', 'department' => 6], 
            ['name' => 'Ms. Pepper Potts', 'department' => 2], 
        ];

        $instructors = [];
        foreach ($instructorsData as $data) {
            $instructors[] = Instructor::firstOrCreate(
                ['name' => $data['name']], 
                ['department' => $data['department']],
                ['start_time' => '08:00:00'],
                ['end_time' => '10:00:00'],
            );
        }

        // 3. Create Evaluation Cycles WITH DATES
        
        // Active Cycle (Now)
        $activeCycle = EvaluationCycle::firstOrCreate(
            ['name' => '1st Semester 2024-2025'],
            [
                'start_date' => Carbon::now()->subMonth(), // Started a month ago
                'end_date' => Carbon::now()->addMonth(),   // Ends a month from now
                'is_active' => true
            ]
        );

        // Inactive Cycle (Past)
        EvaluationCycle::firstOrCreate(
            ['name' => '2nd Semester 2023-2024'],
            [
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->subMonths(2),
                'is_active' => false
            ]
        );

        // 4. Generate Mock Evaluations
        if ($students->count() >= 1) {
            $this->createMockEvaluation($activeCycle, $students[0], $instructors[0], 4); 
            
            if (isset($students[1])) {
                $this->createMockEvaluation($activeCycle, $students[1], $instructors[0], 3);
                $this->createMockEvaluation($activeCycle, $students[1], $instructors[1], 2); 
            }

            if (isset($students[2])) {
                $this->createMockEvaluation($activeCycle, $students[2], $instructors[0], 4); 
            }
        }
    }

    private function createMockEvaluation($cycle, $student, $instructor, $baseRating)
    {
        $exists = Evaluation::where('evaluation_cycle_id', $cycle->id)
            ->where('student_id', $student->id)
            ->where('instructor_id', $instructor->id)
            ->exists();

        if ($exists) return;

        $eval = Evaluation::create([
            'evaluation_cycle_id' => $cycle->id,
            'student_id' => $student->id,
            'instructor_id' => $instructor->id,
            'comments_teacher' => 'test comment for teachers',
            'comments_subject' => 'test comment for subjects',
        ]);

        $answers = [];
        for ($q = 1; $q <= 25; $q++) {
            $rating = rand(max(1, $baseRating - 1), min(4, $baseRating));
            $answers[] = [
                'evaluation_id' => $eval->id,
                'question_index' => $q,
                'rating' => $rating,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        EvaluationAnswer::insert($answers);
    }
}