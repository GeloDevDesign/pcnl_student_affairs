<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HandBookController;
use App\Http\Controllers\ItemController;


Route::inertia('/login', 'auth/login')->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Announcements Routes
Route::get('/', [AnnouncementController::class, 'index'])->middleware(['auth'])->name('home');
Route::middleware(['auth', 'role:admin'])
    ->name('announcements.')
    ->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store'])
            ->name('store');
        Route::patch('/announcements/{announcement}', [AnnouncementController::class, 'update'])
            ->name('update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
            ->name('destroy');
    });

Route::middleware(['auth', 'role:admin'])
    ->name('events.')
    ->group(function () {
        Route::post('/events', [EventController::class, 'store'])
            ->name('store');
        Route::patch('/events/{event}', [EventController::class, 'update'])
            ->name('update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])
            ->name('destroy');
});

Route::middleware(['auth', 'role:admin'])
    ->name('hand-books.')
    ->group(function () {
        Route::post('/hand-books', [HandBookController::class, 'store'])
            ->name('store');
        Route::patch('/hand-books/{handbook}', [HandBookController::class, 'update'])
            ->name('update');
        Route::delete('/hand-books/{handbook}', [HandBookController::class, 'destroy'])
            ->name('destroy');

        Route::get('/hand-books/{handbook}/download', [HandBookController::class, 'download'])
            ->name('download');
    });


Route::get('/lost-and-found', [ItemController::class, 'index'])->middleware(['auth'])->name('lost-and-found');


Route::middleware(['auth', 'role:admin'])
    ->name('items.')
    ->group(function () {
        Route::post('/items', [ItemController::class, 'store'])
            ->name('store');
        Route::patch('/items/{item}', [ItemController::class, 'update'])
            ->name('update');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])
            ->name('destroy');
    });


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




