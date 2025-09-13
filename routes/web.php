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
})->name('login');




Route::get('/', function () {
    return Inertia::render('Home', [
        'pageTitle' => 'Admin - Dashboard',
        'user' =>  [
            'name' => 'Angelo',
            'surname' => 'Serenuela'
        ]
    ]);
})->name('home');
