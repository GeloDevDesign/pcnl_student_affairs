<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\FeedBack;
use App\Models\Form;
use App\Models\Instructor;
use App\Models\Subject;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationCycle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedBackController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // --- 1. EXISTING QUERIES ---
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

        // --- 2. FILTERING ---
        if ($request->filled('search')) {
            switch ($request->page) {
                case 'feedbacks':
                    $eventsQuery->where('id', $request->search);
                    break;
                case 'instructors':
                    if ($request->filled('filter')) $instructorsQuery->where('department', $request->filter);
                    if($request->search !== "1") $instructorsQuery->where('name', 'like', '%'.$request->search.'%');
                    break;
                case 'forms':
                    $formsQuery->where('name', 'like', '%'.$request->search.'%');
                    break;
            }
        }

        // --- 3. NEW FACULTY EVALUATION LOGIC (DATE BASED) ---
        
        // Find a cycle where TODAY is between start_date and end_date
        $today = Carbon::now()->format('Y-m-d');
        
        $activeCycle = EvaluationCycle::where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest()
            ->first();
        
        $adminData = null;
        $studentData = null;

        if ($user->role === 'admin') {
            $cycles = EvaluationCycle::orderBy('created_at', 'desc')->get();
            $selectedCycleId = $request->input('cycle_id', $activeCycle?->id ?? $cycles->first()?->id);

            $results = [];
            if ($selectedCycleId) {
                $results = Instructor::get()->map(function ($instructor) use ($selectedCycleId) {
                    // FETCH EVALUATIONS WITH COMMENTS
                    $evaluations = Evaluation::where('evaluation_cycle_id', $selectedCycleId)
                                ->where('instructor_id', $instructor->id)
                                ->get(); // Get full collection to extract comments
                    
                    $evalIds = $evaluations->pluck('id');
                    $avg = $evalIds->isEmpty() ? 0 : EvaluationAnswer::whereIn('evaluation_id', $evalIds)->avg('rating');

                    // FILTER COMMENTS (Remove empty ones)
                    $comments = $evaluations->map(function($eval) {
                        return [
                            'teacher' => $eval->comments_teacher,
                            'subject' => $eval->comments_subject
                        ];
                    })->filter(function($c) {
                        return !empty($c['teacher']) || !empty($c['subject']);
                    })->values();

                    return [
                        'id' => $instructor->id,
                        'instructor' => $instructor->name,
                        'department' => $instructor->department_name,
                        'respondents' => $evalIds->count(),
                        'average_rating' => round($avg, 2),
                        'verbal_interpretation' => $this->getVerbalInterpretation($avg),
                        'comments' => $comments // <--- PASS COMMENTS HERE
                    ];
                });
            }

            $adminData = [
                'cycles' => $cycles,
                'selected_cycle_id' => (int)$selectedCycleId,
                'results' => $results
            ];
        } else {
            $evalInstructors = [];
            // Only load instructors if we have a valid date-based cycle
            if ($activeCycle) {
                $evalInstructors = Instructor::get()->map(function ($instructor) use ($user, $activeCycle) {
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
            $studentData = [
                'instructors' => $evalInstructors,
                'form_data' => $this->getEvaluationFormStructure()
            ];
        }

        return Inertia::render('evaluate/index', [
            'pageTitle' => 'PCNL - Evaluate',
            'currentFilter' => $request->filter ?? null,
            'subjects' => Subject::latest()->get()->toArray(),
            'events' => $eventsQuery->paginate(10)->onEachSide(1),
            'instructors' => $instructorsQuery->paginate(10)->onEachSide(1),
            'forms' => $formsQuery->paginate(10)->onEachSide(1),
            'active_cycle' => $activeCycle,
            'admin_data' => $adminData,
            'student_data' => $studentData,
        ]);
    }

    public function storeEvaluation(Request $request)
    {
        $request->validate(['ratings' => 'required|array|min:25']);
        
        DB::transaction(function () use ($request) {
            // Double check date validity on submission
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
            foreach($request->ratings as $q => $r) {
                $answers[] = ['evaluation_id' => $eval->id, 'question_index' => $q, 'rating' => $r, 'created_at' => now(), 'updated_at' => now()];
            }
            EvaluationAnswer::insert($answers);
        });

        return redirect()->back()->with('success', 'Evaluation Submitted!');
    }

    // --- UPDATED: STORE CYCLE WITH DATES ---
    public function storeCycle(Request $request)
    {
        if($request->user()->role !== 'admin') abort(403);

        // Validate the dates
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Create the new cycle with the date range
        // We don't strictly need to deactivate old ones (is_active => false) 
        // because the system now checks if the current date is within the start/end range.
        EvaluationCycle::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true
        ]);

        return back()->with('success', 'New evaluation cycle scheduled!');
    }

    // --- EXISTING METHODS (Store Feedback, etc) ---
    
    public function store(Request $request) {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ratings' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);
        $event = Event::findOrFail($validated['event_id']);
        if ($event->is_feedback) return back()->withErrors('Feedback already given.');
        $request->user()->feedbacks()->create($validated);
        return back()->with('success', 'Feedback submitted.');
    }

    public function update(Request $request, FeedBack $feedBack) {
        $feedBack->update($request->validate(['ratings'=>'required|integer','comments'=>'nullable|string']));
        return back()->with('success', 'Updated.');
    }
    
    public function destroy(FeedBack $feedBack) {
        $feedBack->delete();
        return back()->with('success', 'Deleted.');
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
                    1 => "My teacher is pleasant and refined in his/her actions and words and avolds irritating and disturbing mannerisms and movements.",
                    2 => "My teacher is respectable, well-dressed, well-groomed and behaves professionally.",
                    3 => "My teacher is physically and mentally alert and active",
                    4 => "My teacher shows willingness and enthusiasm to help me understand the lesson even after class hours",
                    5 => "I can apply what my teacher teaches to real life situations.",
                    6 => "My teacher shows enthusiasm to work/ to teach and finish tasks and shows pride and joy inhis/her profession.",
                    7 => "I feel accepted and respected as an individual by my teacher",
                    8 => "I see my teacher as a role model for positive behavior.",
                    9 => " I feel free and confident to approach my teacher about academic matters.",
                    10 => "My teacher gives constructive comments and does not embarrass student.",
                    11 => "My teacher inspires me to examine other learning resources to help me gain a better and deeper understanding of the lesson.",
                ]],
                ['title' => 'MASTERY OF THE SUBJECT', 'questions' => [
                    12 => "My teacher shows mastery of the subject matter by providing clear explanations and enough examples to make the lesson easy to understand.",
                    13 => "My teacher introduces the lesson in an interesting manner and presents it in a well-organized way.",
                    14 => "My teacher mentions relevant, current and up-to-date information on the subject matter.",
                    15 => "My teacher uses grammatically correct language.",
                    16 => "My teacher can effectively communicate important concepts of the lesson.",
                    17 => "My teacher points at the relevance of the subject matter to my future profession.",
                ]],
                ['title' => 'CLASSROOM MANAGEMENT', 'questions' => [
                    18 => "My teacher meets our class regularly and uses an efficient method of performing class activities to avoid waste of time and effort.",
                    19 => "My teacher gives and discusses the syllabus/ course outline on the first week of classes.",
                    20 => "My teacher informs us of the coverage/ objective/ overview of the day's lesson and focuses on these in the development of the lesson.",
                    21 => "I get encouragement from my teacher to actively participate in teaching- learning activities, to think critically and analytically and to ask questlons",
                    22 => "My teachers gives enough and accurate evaluation of students' performancele (eg. class participation, quizzes, assignments, tests and other course requirements) and returns properly corrected papers within one or two weeks after the quiz/examinations or submission of the assignment",
                    23 => "My teacher uses adequate instructional materials and appropriate teaching strategies that make the lesson easy to understand",
                    24 => "My teacher enforces classroom policies uniformly to maintain a classroom situation appropriate to learning",
                    25 => "My teacher speaks in a modulated voice, loud and clear enough to be heard by the students.",
                ]]
            ]
        ];
    }
}