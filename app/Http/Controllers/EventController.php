<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|min:5|max:255',
            'description' => 'required|string',
            'date'        => 'required|date',
        ]);



        $request->user()->events()->create($validated);

        return redirect()->back()->with('success', 'Event created successfully.');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'       => 'required|string|min:5|max:255',
            'description' => 'required|string',
            'date'        => 'required|date',
        ]);

        $event->update($validated);

        return redirect()->back()->with('success', 'Event updated successfully.');
    }


    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->back()->with('success', 'Event deleted successfully.');
    }
}
