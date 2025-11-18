<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BackupController extends Controller
{
    public function index()
    {
        $pageTitle = 'Backups';
        return Inertia::render('backups/index', compact('pageTitle'));
    }



    public function destroy($id)
    {
        // Logic to delete a specific backup
    }
}
