<?php

namespace App\Http\Controllers;

use App\Models\FeedBack;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class FeedBackController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        $eventsQuery = Event::with([
            'feedbacks' => fn($q) => $q->where('user_id', '!=', $user->id),
            'userFeedback' => fn($q) => $q->where('user_id', $user->id),
            'user',
        ])->latest();


        // Filter by event ID if a filter is provided
        if ($request->filled('filters')) {
            $eventsQuery->where('id', $request->filters);
        }

        // dd($eventsQuery->get()->toArray());

        $events = $eventsQuery->paginate(10)->withQueryString();

        return Inertia::render('evaluate/index', [
            'pageTitle' => 'PCNL - Evaluate',
            'events'    => $events,
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
            'comment'  => 'nullable|string|max:1000', // <-- singular to match front end
        ]);

        // Check if the current user has already left feedback for this event
        $alreadyFeedback = $request->user()
            ->feedbacks()
            ->where('event_id', $validated['event_id'])
            ->exists();

        if ($alreadyFeedback) {
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
