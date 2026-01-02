<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationCycle;
use App\Models\Event;
use App\Models\FeedBack;
use App\Models\Form;
use App\Models\Instructor;
use App\Models\Subject;
use App\Models\EvaluationQuestion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FeedBackController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // --- 1. EXISTING SEARCH & FILTER LOGIC (KEPT INTACT) ---
        $instructorsQuery = Instructor::with(['subjects'])->latest();
        $formsQuery = Form::with(['user'])->latest();

        $eventsQuery = Event::query()
            ->when($user->isAdmin(), fn ($q) => $q->with(['feedbacks.user', 'user']))
            ->unless($user->isAdmin(), fn ($q) => $q->with(['userFeedback.user', 'user']))
            ->withExists([
                'feedbacks as is_feedback' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
            ])
            ->withCount('feedbacks')
            ->withAvg('feedbacks', 'ratings')
            ->latest();

        if ($request->filled('search')) {
            switch ($request->page) {
                case 'feedbacks':
                    $eventsQuery->where('id', $request->search);
                    break;
                case 'instructors':
                    if ($request->filled('filter')) {
                        $instructorsQuery->where('department', $request->filter);
                    }
                    if ($request->search !== '1') {
                        $instructorsQuery->where('name', 'like', '%' . $request->search . '%');
                    }
                    break;
                case 'forms':
                    $formsQuery->where('name', 'like', '%' . $request->search . '%');
                    break;
            }
        }

        // --- 2. FACULTY EVALUATION LOGIC ---
        $today = Carbon::now()->format('Y-m-d');
        $activeCycle = EvaluationCycle::where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest()
            ->first();

        $adminData = null;
        $studentData = null;

        // [UPDATED] Fetch Dynamic Questions from DB (Grouped by Category for Frontend)
        $questionnaire = EvaluationQuestion::get()->groupBy('category');
        
        // [UPDATED] Fetch All Questions flat collection for Calculations
        $allQuestions = EvaluationQuestion::all();

        // Weighted Configuration
        $categoriesConfig = [
            'TEACHERS PERSONALITY'   => 0.3,
            'MASTERY OF THE SUBJECT' => 0.4,
            'CLASSROOM MANAGEMENT'   => 0.3,
        ];

        if ($user->role === 'admin') {
            $cycles = EvaluationCycle::orderBy('created_at', 'desc')->get();
            $selectedCycleId = $request->input('cycle_id', $activeCycle?->id ?? $cycles->first()?->id);

            $results = [];
            if ($selectedCycleId) {
                $results = Instructor::with('subjects')->get()->map(function ($instructor) use ($selectedCycleId, $allQuestions, $categoriesConfig) {
                    $evaluations = Evaluation::with('student')
                        ->where('evaluation_cycle_id', $selectedCycleId)
                        ->where('instructor_id', $instructor->id)
                        ->get();

                    $evalIds = $evaluations->pluck('id');
                    $respondents = $evalIds->count();

                    $categoryScores = [];
                    $finalWeightedRating = 0;

                    foreach ($categoriesConfig as $catName => $weight) {
                        // Dynamically find Question IDs for this category
                        $qIds = $allQuestions->where('category', $catName)->pluck('id');

                        if ($respondents > 0 && $qIds->isNotEmpty()) {
                            $sum = EvaluationAnswer::whereIn('evaluation_id', $evalIds)
                                ->whereIn('question_index', $qIds) // Matches DB ID
                                ->sum('rating');

                            $numItems = $qIds->count();
                            // Calculation: Average per item, then weighted
                            $avg = ($sum / $numItems / $respondents);
                            $weighted = $avg * $weight;

                            // Map to simple keys for Frontend Table (personality, mastery, management)
                            if (str_contains($catName, 'PERSONALITY')) $categoryScores['personality'] = round($weighted, 2);
                            if (str_contains($catName, 'MASTERY')) $categoryScores['mastery'] = round($weighted, 2);
                            if (str_contains($catName, 'MANAGEMENT')) $categoryScores['management'] = round($weighted, 2);

                            $finalWeightedRating += $weighted;
                        } else {
                            if (str_contains($catName, 'PERSONALITY')) $categoryScores['personality'] = 0;
                            if (str_contains($catName, 'MASTERY')) $categoryScores['mastery'] = 0;
                            if (str_contains($catName, 'MANAGEMENT')) $categoryScores['management'] = 0;
                        }
                    }

                    return [
                        'id' => $instructor->id,
                        'instructor' => $instructor->name,
                        'department' => $instructor->department_name,
                        'respondents' => $respondents,
                        'category_scores' => $categoryScores,
                        'average_rating' => round($finalWeightedRating, 2),
                        'verbal_interpretation' => $this->getVerbalInterpretation($finalWeightedRating),
                        'comments' => $evaluations->map(fn($e) => [
                            'student_name' => $e->student->first_name . ' ' . $e->student->last_name ?? 'Student',
                            'teacher' => $e->comments_teacher,
                            'subject' => $e->comments_subject,
                        ])->filter(fn($c) => !empty($c['teacher']) || !empty($c['subject']))->values(),
                        'subjects' => $instructor->subjects->pluck('name')->take(2)->join(', '),
                    ];
                });
            }

            $adminData = [
                'cycles' => $cycles,
                'selected_cycle_id' => (int) $selectedCycleId,
                'results' => $results,
                'form_data' => $questionnaire, // Pass DB data to admin view
            ];
        } else {
            // --- STUDENT LOGIC ---
            $evalInstructors = [];

            if ($activeCycle) {
                $evalInstructors = Instructor::with('subjects')->get()->map(function ($instructor) use ($user, $activeCycle, $allQuestions, $categoriesConfig) {
                    $evaluation = Evaluation::where('evaluation_cycle_id', $activeCycle->id)
                        ->where('student_id', $user->id)
                        ->where('instructor_id', $instructor->id)
                        ->first();

                    $data = [
                        'id' => $instructor->id,
                        'name' => $instructor->name,
                        'department' => $instructor->department_name,
                        'is_evaluated' => (bool) $evaluation,
                        'subjects' => $instructor->subjects->pluck('name')->take(2)->join(', '),
                        'result' => null,
                    ];

                    if ($evaluation) {
                        $answers = EvaluationAnswer::where('evaluation_id', $evaluation->id)
                            ->pluck('rating', 'question_index')
                            ->toArray();

                        $scores = [];
                        $total = 0;

                        foreach ($categoriesConfig as $catName => $weight) {
                            $qIds = $allQuestions->where('category', $catName)->pluck('id');

                            if ($qIds->isNotEmpty()) {
                                $sum = EvaluationAnswer::where('evaluation_id', $evaluation->id)
                                    ->whereIn('question_index', $qIds)
                                    ->sum('rating');

                                $numItems = $qIds->count();
                                $avg = $sum / $numItems;
                                $weighted = $avg * $weight;

                                if (str_contains($catName, 'PERSONALITY')) $scores['personality'] = round($weighted, 2);
                                if (str_contains($catName, 'MASTERY')) $scores['mastery'] = round($weighted, 2);
                                if (str_contains($catName, 'MANAGEMENT')) $scores['management'] = round($weighted, 2);

                                $total += $weighted;
                            }
                        }

                        $data['result'] = [
                            'ratings' => $answers,
                            'category_scores' => $scores,
                            'average_rating' => round($total, 2),
                            'verbal_interpretation' => $this->getVerbalInterpretation($total),
                            'comments_teacher' => $evaluation->comments_teacher,
                            'comments_subject' => $evaluation->comments_subject,
                        ];
                    }

                    return $data;
                });
            }
            $studentData = [
                'instructors' => $evalInstructors,
                'form_data' => $questionnaire, // Pass DB data to student view
            ];
        }

        return Inertia::render('evaluate/index', [
            'pageTitle' => 'Faculty Evaluation',
            'currentFilter' => $request->filter ?? null,
            'subjects' => Subject::latest()->get(),
            'events' => $eventsQuery->paginate(10)->onEachSide(1),
            'instructors' => $instructorsQuery->paginate(10)->onEachSide(1),
            'forms' => $formsQuery->paginate(10)->onEachSide(1),
            
            // [IMPORTANT] This passes the DB questions to the frontend
            'questionnaire' => $questionnaire, 
            
            'active_cycle' => $activeCycle,
            'admin_data' => $adminData,
            'student_data' => $studentData,
        ]);
    }

    // --- UPDATED: Question Store to support Edit (UpdateOrCreate) ---
    public function storeQuestion(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:evaluation_questions,id',
            'category' => 'required|string',
            'question_text' => 'required|string',
        ]);

        // If 'id' is present, it updates; otherwise, it creates new.
        EvaluationQuestion::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'category' => $data['category'],
                'question_text' => $data['question_text']
            ]
        );

        return back()->with('success', 'Question saved successfully');
    }

    public function deleteQuestion($id)
    {
        EvaluationQuestion::findOrFail($id)->delete();
        return back()->with('success', 'Question deleted');
    }

    public function storeEvaluation(Request $request)
    {
        // Removed strict 25 limit, just ensures it's an array
        $request->validate(['ratings' => 'required|array|min:1']);

        DB::transaction(function () use ($request) {
            $today = Carbon::now()->format('Y-m-d');
            $activeCycle = EvaluationCycle::where('is_active', true)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->firstOrFail();

            $eval = Evaluation::create([
                'evaluation_cycle_id' => $activeCycle->id,
                'student_id' => $request->user()->id,
                'instructor_id' => $request->instructor_id,
                'comments_teacher' => $request->comments_teacher,
                'comments_subject' => $request->comments_subject,
            ]);

            $answers = [];
            foreach ($request->ratings as $q => $r) {
                // $q is the Question Database ID
                $answers[] = [
                    'evaluation_id' => $eval->id,
                    'question_index' => $q,
                    'rating' => $r,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            EvaluationAnswer::insert($answers);
        });

        return back()->with('success', 'Evaluation Submitted!');
    }

    // --- EXISTING CYCLE & FEEDBACK LOGIC (KEPT AS IS) ---

    public function storeCycle(Request $request)
    {
        EvaluationCycle::where('is_active', true)->update(['is_active' => 0]);

        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        EvaluationCycle::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
        ]);

        return back()->with('success', 'New evaluation cycle scheduled!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ratings' => 'required|numeric|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
            'survey_details' => 'nullable',
        ]);

        $event = Event::findOrFail($validated['event_id']);

        if ($event->is_feedback) {
            return back()->withErrors('Feedback already given.');
        }

        if (!empty($request->survey_details)) {
            $details = 'Survey Breakdown: ' . json_encode($request->survey_details) . "\n---\nUser Comment: ";
            $validated['comments'] = $details . ($validated['comments'] ?? 'No text comment.');
        }

        $dataToSave = collect($validated)->except(['survey_details'])->toArray();
        $request->user()->feedbacks()->create($dataToSave);

        return back()->with('success', 'Feedback submitted.');
    }

    public function update(Request $request, FeedBack $feedBack)
    {
        $feedBack->update($request->validate(['ratings' => 'required|integer', 'comments' => 'nullable|string']));
        return back()->with('success', 'Updated.');
    }

    public function destroy(FeedBack $feedBack)
    {
        $feedBack->delete();
        return back()->with('success', 'Deleted.');
    }

    private function getVerbalInterpretation($score)
    {
        if ($score >= 3.51) return 'Excellent';
        if ($score >= 3.01) return 'Very Good';
        if ($score >= 2.51) return 'Good';
        if ($score >= 2.01) return 'Fair';
        return 'Poor';
    }
}