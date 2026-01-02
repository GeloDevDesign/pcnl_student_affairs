<?php


// app/Http/Controllers/QuestionnaireController.php
namespace App\Http\Controllers;

use App\Models\EvaluationQuestion;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    // Create or Update a question
    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:evaluation_questions,id',
            'category' => 'required|string',
            'question_text' => 'required|string',
        ]);

        // If ID exists, update; otherwise, create new
        EvaluationQuestion::updateOrCreate(['id' => $data['id']], $data);

        return back()->with('success', 'Questionnaire updated!');
    }

    // Delete a question
    public function destroy($id)
    {
        EvaluationQuestion::findOrFail($id)->delete();
        return back()->with('success', 'Question deleted!');
    }
}

