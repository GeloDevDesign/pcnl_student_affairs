<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;



Route::inertia('/login', 'auth/login')->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Announcements Routes
Route::get('/', [AnnouncementController::class, 'index'])->middleware(['auth'])->name('home');
Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware(['auth'])->name('announcements.store');
Route::patch('/announcements/{announcements}', [AnnouncementController::class, 'update'])->middleware(['auth'])->name('announcements.store');
Route::delete('/announcements', [AnnouncementController::class, 'destroy'])->middleware(['auth'])->name('announcements.store');


Route::get('/evaluate', function () {
    return Inertia::render('evaluate/index', [
        'pageTitle' => 'PCNL - Evaluate'
    ]);
})->middleware(['auth'])
    ->name('evaluate');



Route::get('/scc-officers', function () {
    return Inertia::render('ssc-officers/index', [
        'pageTitle' => 'PCNL - SCC Officers'
    ]);
})->middleware(['auth'])
    ->name('evaluate');


Route::get('/concerns', function () {
    return Inertia::render('concerns/index', [
        'pageTitle' => 'PCNL - Concerns'
    ]);
})->middleware(['auth'])
    ->name('evaluate');



Route::get('/lost-found', function () {
    return Inertia::render('lost-and-found/index', [
        'pageTitle' => 'PNCL - Lost & Found'
    ]);
})->middleware(['auth'])
    ->name('evaluate');
