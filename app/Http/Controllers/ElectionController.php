<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Illuminate\Http\Request;

class ElectionController extends Controller
{



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $election = Election::create($validated);
        return redirect()->back()->with('success', 'Election created successfully!');
    }

    public function update(Request $request, Election $election)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:0,1,2'
        ]);


        $election->update($validated);
        return redirect()->back()->with('success', 'Election updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Election $election)
    {
        //
    }
}
