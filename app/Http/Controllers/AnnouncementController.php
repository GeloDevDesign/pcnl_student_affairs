<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\HandBook;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        $announcement = Announcement::with('user')
            ->latest()
            ->when($request->page === 'announcement' && $request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('details', 'like', '%' . $request->search . '%');
                });
            });

        $announcementCount = Announcement::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

        $event = Event::with('user')
            ->latest()
            ->when($request->page === 'event' && $request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            });

        $eventCount = Event::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

        // Get only the latest handbook (single record)
        $handBook = HandBook::with('user')
            ->latest()
            ->when($request->page === 'hand-books' && $request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            })
            ->first(); // Get only the first/latest record

        $itemCount = Item::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

        return Inertia::render('dashboard/index', [
            'pageTitle'         => 'PCNL - Dashboard',
            'handBook'          => $handBook, // Changed from handBooks to handBook (singular)
            'announcements'     => $announcement->paginate(10)->onEachSide(1),
            'events'            => $event->paginate(10)->onEachSide(1),
            'announcementCount' => $announcementCount,
            'eventCount'        => $eventCount,
            'itemCount'         => $itemCount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|min:5',
            'details' => 'required|string',
            'image_url'   => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'publish_at' => 'required|date',
        ]);

        if ($request->hasFile('image_url')) {
            $filename = time() . '-' . $request->file('image_url')->getClientOriginalName();
            $validated['image_url'] = $request->file('image_url')->storeAs('items', $filename, 'public');
        }

        $validated['publish_at'] = $validated['publish_at']
            ? Carbon::parse($validated['publish_at'])->format('Y-m-d')
            : null;

        $request->user()->announcements()->create($validated);

        return redirect()->back()->with('success', 'Announcement created successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|min:5',
            'details' => 'required|string',
            'image_url'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'publish_at'        => 'required|date',
        ]);

        if ($request->hasFile('image_url')) {
            $filename = time() . '-' . $request->file('image_url')->getClientOriginalName();
            $validated['image_url'] = $request->file('image_url')->storeAs('items', $filename, 'public');
        }

        $validated['publish_at'] = $validated['publish_at']
            ? Carbon::parse($validated['publish_at'])->format('Y-m-d')
            : null;

        $announcement->update($validated);

        return redirect()->back()->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image_url) {
            Storage::disk('public')->delete($announcement->image_url);
        }

        $announcement->delete();

        return redirect()->back()->with('success', 'Announcement deleted successfully.');
    }
}