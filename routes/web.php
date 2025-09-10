<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'pageTitle' => 'Admin - Dashboard',
        'user' =>  [
            'name' => 'Angelo',
            'surname' => 'Serenuela'
        ]
    ]);
})->name('home');
