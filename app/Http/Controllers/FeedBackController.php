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
        $events    = Event::with('user')->get();

        if ($request->user()->isAdmin()) {
            $feedbacks = Event::with(['event', 'user']);
        } else {
            $feedbacks = $request->user()
                ->feedbacks()
                ->with('event');
        }

        if ($request->filled('filters')) {
            $feedbacks->whereHas('event', function ($q) use ($request) {
                $q->where('id', $request->filters);
            });
        }

        $feedbacks->latest()->paginate(10);


        return Inertia::render('evaluate/index', [
            'pageTitle' => 'PCNL - Evaluate',
            'feedbacks' => $feedbacks,
            'events'    => $events
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'ratings'  => 'required|integer|min:1max:5',
            'comment'  => 'nullable|string|max:1000',
        ]);

        $request->user()->feedbacks()->create($validated);


        return redirect()->back()->with('success', 'Feedback submitted successfully.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeedBack $feedBack)
    {

        $validated = $request->validate([
            'ratings'  => 'required|integer|min:1max:5',
            'comment'  => 'nullable|string|max:1000',
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
