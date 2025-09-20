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

        $query->when($request->input('search'), function ($q, $search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('details', 'like', '%' . $search . '%');
            });
        });

        return Inertia::render('dashboard/index', [
            'pageTitle' => 'PCNL - Dashboard',
            'announcement' => $query->paginate(10),
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
