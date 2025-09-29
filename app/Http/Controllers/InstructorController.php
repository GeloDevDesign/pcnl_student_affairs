<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:2|max:255',
            'department' => 'required|min:2|max:255',
        ]);

        $request->user()->instructors()->create($validated);

        return redirect()->back()->with('success', 'Instructor created successfully!');
    }





    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'name' => 'required|min:2|max:255',
            'department' => 'required|min:2|max:255',
        ]);

        $instructor->update($validated);

        return redirect()->back()->with('success', 'Instructor updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor)
    {
        $instructor->delete();

        return redirect()->back()->with('success', 'Instructor deleted successfully!');
    }
}
