<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreatedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageTitle = 'Student List';

        if ($request->filled('search')) {
            $students = User::whereIn('role', ['student', 'admin'])
                ->where(function ($query) use ($request) {
                    $searchTerm = $request->input('search');
                    $query->where('first_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('id_number', 'like', '%' . $searchTerm . '%');
                })
                ->orderBy('first_name')
                ->paginate(10)
                ->withQueryString();
        } else {
            $students = User::where('role', 'student')
                ->orderBy('first_name')
                ->paginate(10);
        }


        return inertia('user-management/index', compact('pageTitle', 'students'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        // Department mapping (ID to name)
        $departments = [
            1 => 'BSA',
            2 => 'BSBA',
            3 => 'BSCRIM',
            4 => 'BSIT',
            5 => 'BSCE',
            6 => 'BEE',
        ];

        // Base validation rules
        $rules = [
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'role'        => 'required|in:admin,student',
            'email'       => 'required|email|unique:users,email',
            'department'  => 'nullable|integer|in:' . implode(',', array_keys($departments)),
            'id_number'   => 'nullable|string|unique:users,id_number',
        ];

        // If the role is student, department & id_number required
        if ($request->role === User::TYPE_STUDENT) {
            $rules['department'] = 'required|integer|in:' . implode(',', array_keys($departments));
            $rules['id_number'] = 'required|string|unique:users,id_number';
        }

        $validated = $request->validate($rules);

        // Get department name based on selected ID
        $departmentName = $validated['department']
            ? $departments[$validated['department']]
            : null;

        // Generate a random password
        $password = Str::random(8);

        // Create user
        $user = User::create([
            'first_name'  => $validated['first_name'],
            'last_name'   => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'department'  => $departmentName,
            'id_number'   => $validated['id_number'] ?? null,
            'role'        => $validated['role'],
            'email'       => $validated['email'],
            'password'    => bcrypt($password),
        ]);

        // If student and no id_number was provided, auto-generate one
        if ($user->isStudent() && empty($user->id_number)) {
            $user->id_number = 'STU-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $user->save();
        }

        // Send email with credentials
        Mail::to($user->email)->send(new UserCreatedMail(
            $user->first_name . ' ' . $user->last_name,
            $user->id_number ?? 'N/A',
            $user->email,
            $password
        ));

        return redirect()->back()->with('success', 'User created and email sent successfully!');
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'department'  => 'required|string|max:150',
            'email'       => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'id_number'   => ['required', 'string', 'max:50', Rule::unique('users', 'id_number')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', 'Student deleted successfully.');
    }
}
