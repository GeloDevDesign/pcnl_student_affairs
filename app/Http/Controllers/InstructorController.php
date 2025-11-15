<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\DepartmentList;

class InstructorController extends Controller
{




    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:2|max:255|unique:instructors,name',
            'department' => ['nullable', 'integer', Rule::in(DepartmentList::ids())],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        // Create instructor
        $instructor = $request->user()->instructors()->create([
            'name' => $validated['name'],
            'department' => $validated['department'] ?? null,
        ]);

        // Attach subjects
        if (!empty($validated['subject_ids'])) {
            $instructor->subjects()->sync($validated['subject_ids']);
        }

        return redirect()->back()->with('success', 'Instructor created successfully!');
    }





    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'name' => ['required','min:2','max:255', Rule::unique('instructors','name')->ignore($instructor->id)],
            'department' => ['nullable', 'integer', Rule::in(DepartmentList::ids())],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        // Update instructor info
        $instructor->update([
            'name' => $validated['name'],
            'department' => $validated['department'] ?? null,
        ]);

        // Sync subjects
        $instructor->subjects()->sync($validated['subject_ids'] ?? []);

        return redirect()->back()->with('success', 'Instructor updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor)
    {
        $instructor->subjects()->detach(); 
        $instructor->delete();

        return redirect()->back()->with('success', 'Instructor deleted successfully!');
    }
}
