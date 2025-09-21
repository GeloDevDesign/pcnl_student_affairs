<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\HandBook;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcement  = Announcement::with('user')->latest();


        if ($request->page === 'announcement') {
            $announcement->when($request->filled('search'), function ($q) use ($request) {
                $q->whereAny(['title', 'details'], 'like', '%' . $request->search . '%');
            });
        }


        $event = Event::with('user')->latest();
        if ($request->page === 'event') {
            $event->when($request->filled('search'), function ($q) use ($request) {
                $q->whereAny(['title', 'description'], 'like', '%' . $request->search . '%');
            });
        }


        $handBooks = HandBook::with('user')->latest();
        if ($request->page === 'hand-books') {
            $event->when($request->filled('search'), function ($q) use ($request) {
                $q->whereAny(['title', 'description'], 'like', '%' . $request->search . '%');
            });
        }


        return Inertia::render('dashboard/index', [
            'pageTitle' => 'PCNL - Dashboard',
            'handBooks' => $handBooks->paginate(10)->onEachSide(1),
            'announcements' => $announcement->paginate(10)->onEachSide(1),
            'events' => $event->paginate(10)->onEachSide(1),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|min:5',
            'details' => 'required|string',
        ]);


        $request->user()->announcements()->create($validated);

        return redirect()->back()->with('success', 'Announcement created successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|min:5',
            'details' => 'required|string',
        ]);

        $announcement->update($validated);

        return redirect()->back()->with('success', 'Announcement updated successfully.');
    }


    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->back()->with('success', 'Announcement updated successfully.');
    }
}
