<?php

namespace App\Http\Controllers;

use App\Models\PartyList;
use Illuminate\Http\Request;

class PartyListController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'election_id' => 'required|exists:elections,id',
            'name' => 'required|string|min:3',
            'slogan' => 'nullable|string|min:5',
        ]);

        PartyList::create($validated);


        return redirect()->back()->with('success', 'Party List created successfully!');
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PartyList $parties)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3',
            'slogan' => 'nullable|string|min:5',
        ]);

        $parties->update($validated);

        return redirect()->back()->with('success', 'Party List updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartyList $parties)
    {
        $parties->delete();

        return redirect()->back()->with('success', 'Party List deleted successfully!');
    }
}
