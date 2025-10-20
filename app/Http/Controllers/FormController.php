<?php

namespace App\Http\Controllers;

use App\Models\Form;

use Illuminate\Http\Request;

class FormController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'description' => 'nullable|string',
        ]);

        $form = $request->user()->forms()->create([
            'name' => $request->name,
            'url' => $request->url,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Form attached successfully!');
    }

    public function update(Request $request, Form $form)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'description' => 'nullable|string',
        ]);

        $form->update([
            'name' => $request->name,
            'url' => $request->url,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Form updated successfully!');
    }

    public function destroy(Form $form)
    {
        $form->delete();

        return redirect()->back()->with('success', 'Form detached successfully!');
    }
}
