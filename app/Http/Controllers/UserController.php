<?php

namespace App\Http\Controllers;

use App\Mail\UserCreatedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Department mapping (ID to name) - made constant for accessibility
    protected const DEPARTMENTS = [
        1 => 'BSA',
        2 => 'BSBA',
        3 => 'BSCRIM',
        4 => 'BSIT',
        5 => 'BSCE',
        6 => 'BEE',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageTitle = 'Student List';

        $filterRole = $request->filter ?? 'student';

        $query = User::where('role', $filterRole);

        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $searchTerm = $request->input('search');
                $query->where('first_name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('last_name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$searchTerm.'%')
                    ->orWhere('id_number', 'like', '%'.$searchTerm.'%');
            });
        }

        $users = $query->orderBy('last_name')->paginate(10)->withQueryString();
        $currentFilter = $filterRole;

        return inertia('user-management/index', compact('pageTitle', 'users', 'currentFilter'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function storeStudent(Request $request)
    {
        // Student-specific validation rules
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'department' => 'required|integer|in:'.implode(',', array_keys(self::DEPARTMENTS)),
            'id_number' => [
                'required',
                'string',
                'unique:users,id_number',
                'regex:/^[0-9\-]+$/',
                'min:6',
                'max:10',
            ],
        ]);

        $validated['role'] = 'student';

        $this->createUser($validated);

        return redirect()->back()->with('success', 'Student created and email sent successfully!');
    }

    /**
     * Store a newly created administrator in storage.
     */
    public function storeAdmin(Request $request)
    {
        // Admin-specific validation rules (less strict on department/ID)
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'id_number' => 'nullable|string|unique:users,id_number',
        ]);

        $validated['department'] = null;
        $validated['role'] = 'admin';

        $this->createUser($validated);

        return redirect()->back()->with('success', 'Administrator created and email sent successfully!');
    }

    /**
     * Private helper method to handle the common logic of user creation and notification.
     */
    private function createUser(array $validated): User
    {
        // Get department name based on selected ID
        $departmentName = $validated['department']
            ? self::DEPARTMENTS[$validated['department']]
            : null;

        // Generate a random password
        $password = Str::random(8);

        // Create user
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'department' => $departmentName,
            'id_number' => $validated['id_number'] ?? null,
            'role' => $validated['role'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
        ]);

        // If student and no id_number was provided, auto-generate one
        if ($user->role === 'student' && empty($user->id_number)) {
            $user->id_number = 'STU-'.str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $user->save();
        }

        // Send email with credentials
        Mail::to($user->email)->send(new UserCreatedMail(
            $user->first_name.' '.$user->last_name,
            $user->id_number ?? 'N/A',
            $user->email,
            $password
        ));

        return $user;
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, User $user)
    {
        // Base validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(['admin', 'student'])],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'department' => 'nullable|integer|in:'.implode(',', array_keys(self::DEPARTMENTS)),
            'id_number' => [
                'nullable',
                'string',
                Rule::unique('users', 'id_number')->ignore($user->id),
            ],
        ];

        // If student → department and id_number become REQUIRED
        // Note: Assumes User::TYPE_STUDENT is defined or is 'student' string
        if ($request->role === 'student') {
            $rules['department'] = 'required|integer|in:'.implode(',', array_keys(self::DEPARTMENTS));
            $rules['id_number'] = [
                'required',
                'string',
                'regex:/^[0-9\-]+$/',
                'min:6',
                'max:10',
                Rule::unique('users', 'id_number')->ignore($user->id),
            ];
        }

        $validated = $request->validate($rules);

        // --- FIX APPLIED HERE: Safely retrieve department ID ---
        $departmentId = data_get($validated, 'department');

        // Convert department ID → department name
        $departmentName = ($departmentId !== null) && array_key_exists($departmentId, self::DEPARTMENTS)
            ? self::DEPARTMENTS[$departmentId]
            : null;

        // Merge converted department into validated data
        // If $departmentName is null (e.g., for an admin where department is not required/set),
        // it correctly sets the field to null in the database.
        $validated['department'] = $departmentName;

        // Update user
        $user->update($validated);

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(User $user)
    {
        // Delete profile photo if exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();

        return redirect()->back()->with('success', 'Student deleted successfully.');
    }

    /**
     * Update authenticated user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:150',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'id_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'id_number')->ignore($user->id)],
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old profile photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new image
            $imagePath = $request->file('profile_image')->store('images', 'public');
            $validated['profile_photo_path'] = $imagePath;
        }

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update authenticated user password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if current password is correct
        if (! Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }
}
