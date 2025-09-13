<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', function () {
    return Inertia::render('Login', [
        'pageTitle' => 'PNCL Student Affairs - Login',
        'user' =>  [
            'name' => 'Angelo',
            'surname' => 'Serenuela'
        ]
    ]);
})->middleware('guest')->name('login');




Route::get('/', function () {
    
    return Inertia::render('Home', [
        'pageTitle' => 'Admin - Dashboard'
    ]);
})->name('home')->middleware(['auth', 'role:admin,student']);
