<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Admin List';
        $query = User::where('role', 'admin');

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

        return Inertia::render('admin/index', compact('pageTitle', 'users'));
    }
}
