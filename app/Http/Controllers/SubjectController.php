<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $subjectsQuery = Subject::latest();

        if ($search) {
            $subjectsQuery->where('name', 'like', "%{$search}%");
        }

        $subjects = $subjectsQuery->paginate(10)->withQueryString();

        return Inertia::render('subjects/index', [
            'subjects' => $subjects,
            'pageTitle' => 'Subject Management'
        ]);
    }

   

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:2|max:255|unique:subjects,name',
        ]);

        Subject::create($validated);

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

   

    public function update(Request $request, Subject $subject)
    {

        $validated = $request->validate([
            'name' => ['required', 'min:2', 'max:255', Rule::unique('subjects')->ignore($subject->id)],
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
