<?php

namespace App\Http\Controllers;

use App\Models\HandBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HandBookController extends Controller
{
    /**
     * Store a newly created handbook.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255|min:5',
            'description' => 'required|string',
            'file_url'        => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Save file to storage/app/public/handbooks
        $filename = time() . '-' . $request->file('file_url')->getClientOriginalName();
        $path = $request->file('file_url')->storeAs('handbooks', $filename, 'public');

        // Create the handbook record linked to the authenticated user
        $request->user()->handBooks()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'file_url'    => $path,
        ]);

        return redirect()->back()->with('success', 'Handbook uploaded successfully!');
    }

    /**
     * Update an existing handbook.
     */
   public function update(Request $request, HandBook $handbook)
{
    $validated = $request->validate([
        'title'       => 'required|string|max:255|min:5',
        'description' => 'required|string',
        // Still validate the input, but we will handle its absence below
        'file_url'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
    ]);

  
    unset($validated['file_url']);
    
 
    if ($request->hasFile('file_url')) {
        
    
        if ($handbook->file_url) {
         
            Storage::disk('public')->delete($handbook->file_url); 
        }

        $filename = time() . '-' . $request->file('file_url')->getClientOriginalName();
     
        $validated['file_url'] = $request->file('file_url')->storeAs('handbooks', $filename, 'public');
    }

    // Update the handbook (the file_url field is only included if a new file was uploaded)
    $handbook->update($validated);

    return redirect()->back()->with('success', 'Handbook updated successfully!');
}

    /**
     * Delete a handbook and its stored file.
     */
    public function destroy(HandBook $handbook)
    {
        if ($handbook->file_url) {
            Storage::disk('public')->delete($handbook->file_url);
        }

        $handbook->delete();

        return redirect()->back()->with('success', 'Handbook deleted successfully!');
    }

    /**
     * Download the handbook file.
     */
    public function download(HandBook $handbook)
    {
        // Check if file exists
        if (!$handbook->file_url || !Storage::disk('public')->exists($handbook->file_url)) {
            return redirect()->back()->withErrors(['error' => 'File not found.']);
        }

        // Relative path stored in the database, e.g. "handbooks/file.pdf"
        $filePath = $handbook->file_url;

        // Custom name for the downloaded file
        $downloadName = $handbook->title . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($filePath, $downloadName);
    }
}
