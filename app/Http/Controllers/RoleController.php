<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'description'  => 'nullable|string|max:255|min:3',
        ]);

        $role = Role::create($validated);

        return redirect()->back()->with('success', 'Role created successfully!');
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'description'  => 'nullable|string|max:255|min:3',
        ]);

        $role->update($validated);
        return redirect()->back()->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully!');
    }
}
