<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FacultyEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activeCycle = EvaluationCycle::where('is_active', true)->first();

        // Common Data
        $props = [
            'active_cycle' => $activeCycle,
            'auth_role' => $user->role, // 'admin' or 'student'
        ];

        // --- ADMIN DATA ---
        if ($user->role === 'admin') {
            $cycles = EvaluationCycle::orderBy('created_at', 'desc')->get();
            $selectedCycleId = $request->input('cycle_id', $activeCycle?->id ?? $cycles->first()?->id);

            $results = [];
            if ($selectedCycleId) {
                // FIX: Removed ::with('user') because instructors no longer have user accounts
                $results = Instructor::get()->map(function ($instructor) use ($selectedCycleId) {
                    $evalIds = Evaluation::where('evaluation_cycle_id', $selectedCycleId)
                                ->where('instructor_id', $instructor->id)->pluck('id');
                    
                    $avg = $evalIds->isEmpty() ? 0 : EvaluationAnswer::whereIn('evaluation_id', $evalIds)->avg('rating');

                    return [
                        'id' => $instructor->id,
                        'instructor' => $instructor->name,
                        'department' => $instructor->department_name,
                        'respondents' => $evalIds->count(),
                        'average_rating' => round($avg, 2),
                        'verbal_interpretation' => $this->getVerbalInterpretation($avg)
                    ];
                });
            }

            $props['admin_data'] = [
                'cycles' => $cycles,
                'selected_cycle_id' => (int)$selectedCycleId,
                'results' => $results
            ];
        } 
        // --- STUDENT DATA ---
        else {
            $instructors = [];
            if ($activeCycle) {
                $instructors = Instructor::get()->map(function ($instructor) use ($user, $activeCycle) {
                    $isEvaluated = Evaluation::where('evaluation_cycle_id', $activeCycle->id)
                        ->where('student_id', $user->id)
                        ->where('instructor_id', $instructor->id)
                        ->exists();
                    return [
                        'id' => $instructor->id,
                        'name' => $instructor->name,
                        'department' => $instructor->department_name,
                        'is_evaluated' => $isEvaluated,
                    ];
                });
            }
            $props['student_data'] = [
                'instructors' => $instructors,
                'form_data' => $this->getEvaluationFormStructure()
            ];
        }

        return Inertia::render('evaluate/eval-forms', $props);
    }

    public function store(Request $request)
    {
        $request->validate(['ratings' => 'required|array|min:25']);
        
        DB::transaction(function () use ($request) {
            $activeCycle = EvaluationCycle::where('is_active', true)->firstOrFail();
            
            $eval = Evaluation::create([
                'evaluation_cycle_id' => $activeCycle->id,
                'student_id' => $request->user()->id,
                'instructor_id' => $request->instructor_id,
                'comments_teacher' => $request->comments_teacher,
                'comments_subject' => $request->comments_subject
            ]);

            $answers = [];
            foreach($request->ratings as $q => $r) {
                $answers[] = ['evaluation_id' => $eval->id, 'question_index' => $q, 'rating' => $r, 'created_at' => now(), 'updated_at' => now()];
            }
            EvaluationAnswer::insert($answers);
        });

        return redirect()->back()->with('success', 'Evaluation Submitted!');
    }

    public function storeCycle(Request $request)
    {
        if($request->user()->role !== 'admin') abort(403);
        EvaluationCycle::query()->update(['is_active' => false]);
        EvaluationCycle::create(['name' => $request->name, 'is_active' => true]);
        return back()->with('success', 'New cycle started!');
    }

    private function getVerbalInterpretation($score) {
        if ($score >= 3.50) return 'Excellent';
        if ($score >= 2.50) return 'Very Good';
        if ($score >= 1.50) return 'Fair';
        return 'Poor';
    }

    private function getEvaluationFormStructure() {
        return [
            'sections' => [
                ['title' => 'TEACHERS PERSONALITY', 'questions' => [
                    1 => "My teacher is pleasant and refined in his/her actions...",
                    2 => "My teacher is respectable, well-dressed...",
                    3 => "My teacher is physically and mentally alert...",
                    4 => "My teacher shows willingness and enthusiasm...",
                    5 => "I can apply what my teacher teaches to real life...",
                    6 => "My teacher shows enthusiasm to work...",
                    7 => "I feel accepted and respected as an individual...",
                    8 => "I see my teacher as a role model...",
                    9 => "I feel free and confident to approach my teacher...",
                    10 => "My teacher gives constructive comments...",
                    11 => "My teacher inspires me to examine other resources...",
                ]],
                ['title' => 'MASTERY OF THE SUBJECT', 'questions' => [
                    12 => "My teacher shows mastery of the subject matter...",
                    13 => "My teacher introduces the lesson in an interesting manner...",
                    14 => "My teacher mentions relevant, current information...",
                    15 => "My teacher uses grammatically correct language.",
                    16 => "My teacher can effectively communicate important concepts...",
                    17 => "My teacher points at the relevance of the subject...",
                ]],
                ['title' => 'CLASSROOM MANAGEMENT', 'questions' => [
                    18 => "My teacher meets our class regularly...",
                    19 => "My teacher gives and discusses the syllabus...",
                    20 => "My teacher informs us of the coverage/objective...",
                    21 => "I get encouragement from my teacher to participate...",
                    22 => "My teacher gives enough and accurate evaluation...",
                    23 => "My teacher uses adequate instructional materials...",
                    24 => "My teacher enforces classroom policies uniformly...",
                    25 => "My teacher speaks in a modulated voice...",
                ]]
            ]
        ];
    }
}