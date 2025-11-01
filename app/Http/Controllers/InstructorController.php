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
        $departments = [
            1 => 'BSA',
            2 => 'BSBA',
            3 => 'BSCRIM',
            4 => 'BSIT',
            5 => 'BSCE',
            6 => 'BEE',
        ];


        $validated = $request->validate([
            'name' => 'required|min:2|max:255',
            'department'  => 'nullable|integer|in:' . implode(',', array_keys($departments)),
        ]);

        $request->user()->instructors()->create($validated);

        return redirect()->back()->with('success', 'Instructor created successfully!');
    }





    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instructor $instructor)
    {
        $departments = [
            1 => 'BSA',
            2 => 'BSBA',
            3 => 'BSCRIM',
            4 => 'BSIT',
            5 => 'BSCE',
            6 => 'BEE',
        ];


        $validated = $request->validate([
            'name' => 'required|min:2|max:255',
            'department'  => 'nullable|integer|in:' . implode(',', array_keys($departments)),
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
