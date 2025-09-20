<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        return Inertia::render('dashboard/index', [
            'pageTitle' => 'PCNL - Dashboard',
            'announcement' => Announcement::with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',

            'details' => 'required|string',
        ]);


        $request->user()->announcements()->create($validated);

        return redirect()->back()->with('success', 'Announcement created successfully.');
    }
}
