<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ItemController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index(Request $request)
    {
        $items = Item::with('user')->latest();

        // Optional search for lost items
        if ($request->page === 'lost') {
            $items->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            });
        }

        return Inertia::render('lost-and-found/index', [
            'pageTitle' => 'PCNL - Lost & Found',
            'items'     => $items->paginate(10)->onEachSide(1),
        ]);
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|min:5',
            'description' => 'required|string|max:255|min:5',
            'image_url'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);


        // Handle image upload
        if ($request->hasFile('image_url')) {
            $filename = time() . '-' . $request->file('image_url')->getClientOriginalName();
            $path = $request->file('image_url')->storeAs('items', $filename, 'public');
        }

        // Create item linked to authenticated user
        $request->user()->items()->create([
            'name'        => $validated['name'],
            'description' => $validated['description'],
            'image_url'   => $path,
            'status'      => Item::NOT_FOUND,
        ]);

        return redirect()->back()->with('success', 'Item created successfully!');
    }

    /**
     * Update an existing item.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|min:5',
            'description' => 'required|string|max:255|min:5',
            'image_url'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'status'      => 'required|in:0,1,2',
        ]);

        if ($request->hasFile('image_url')) {
            // Delete old file if it exists
            if ($item->image_url) {
                Storage::disk('public')->delete($item->image_url);
            }

            $filename = time() . '-' . $request->file('image_url')->getClientOriginalName();
            $validated['image_url'] = $request->file('image_url')->storeAs('items', $filename, 'public');
        }

        $item->update($validated);

        return redirect()->back()->with('success', 'Item updated successfully!');
    }

    /**
     * Delete an item and its image.
     */
    public function destroy(Item $item)
    {
        if ($item->image_url) {
            Storage::disk('public')->delete($item->image_url);
        }

        $item->delete();

        return redirect()->back()->with('success', 'Item deleted successfully!');
    }
}
