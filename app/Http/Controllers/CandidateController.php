<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|max:255',
            'role_id' => 'required|exists:roles,id',
            'election_id' => 'required|exists:elections,id',
            'party_id' => 'required|exists:party_lists,id',
        ]);

        $request->user()->candidates()->create($validated);

        return redirect()->back()->with('success', 'Candidate assigned successfully!');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'full_name' => 'required|max:255',
            'role_id' => 'required|exists:roles,id',
            'election_id' => 'required|exists:elections,id',
            'party_id' => 'required|exists:party_lists,id',
        ]);


        $candidate->update($validated);

        return redirect()->back()->with('success', 'Candidate updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Candidate $candidate)
    {
        $candidate->delete();

        return redirect()->back()->with('success', 'Candidate deleted successfully!');
    }
}
