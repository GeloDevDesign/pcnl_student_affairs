<?php

namespace App\Http\Controllers;

use App\Models\FeedBack;
use App\Models\Event;
use App\Models\Instructor;
use App\Models\Form;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class FeedBackController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        // Base queries
        $instructorsQuery = Instructor::with(['user'])->latest();
        $formsQuery       = Form::with(['user'])->latest();
        $eventsQuery      = Event::query()->with([
            $user->isAdmin() ? 'feedbacks' : 'userFeedback',
            'user',
        ])
            ->withExists([
                'feedbacks as is_feedback' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }
            ])
            ->withCount('feedbacks')
            ->withAvg('feedbacks', 'ratings')
            ->latest();

        // Apply filters based on request page
        if ($request->filled('search')) {
          
            switch ($request->page) {

                case 'feedbacks':

                    $eventsQuery->where('id', $request->search);
                    break;

                case 'instructors':
                    $instructorsQuery->where('name', 'like', '%' . $request->search . '%');
                    break;

                case 'forms':
                    $formsQuery->where('name', 'like', '%' . $request->search . '%');
                    break;
            }
        }

        return Inertia::render('evaluate/index', [
            'pageTitle'   => 'PCNL - Evaluate',
            'events'      => $eventsQuery->paginate(10)->onEachSide(1),
            'instructors' => $instructorsQuery->paginate(10)->onEachSide(1),
            'forms'       => $formsQuery->paginate(10)->onEachSide(1),
        ]);
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ratings'  => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);

        // Load the Event model so the accessor can work
        $event = Event::findOrFail($validated['event_id']);

        // Use the accessor to check if the user already left feedback
        if ($event->is_feedback) {
            return back()->withErrors('You have already given feedback for this event.');
        }

        // Create the feedback
        $request->user()->feedbacks()->create($validated);

        return back()->with('success', 'Feedback submitted successfully.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeedBack $feedBack)
    {

        $validated = $request->validate([
            'ratings'  => 'required|integer|min:1|max:5',
            'comments'  => 'nullable|string|max:1000',
        ]);

        $feedBack->update($validated);

        return redirect()->back()->with('success', 'Feedback updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeedBack $feedBack)
    {
        $feedBack->delete();
        return redirect()->back()->with('success', 'Feedback deleted successfully.');
    }
}
