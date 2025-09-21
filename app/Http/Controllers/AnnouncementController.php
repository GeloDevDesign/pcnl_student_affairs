<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query  = Announcement::with('user')->latest();

        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->whereAny(['title', 'details'], 'like', '%' . $request->search . '%');
        });

        
        return Inertia::render('dashboard/index', [
            'pageTitle' => 'PCNL - Dashboard',
            'announcement' => $query->paginate(10)->onEachSide(1),
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
