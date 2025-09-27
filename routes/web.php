<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HandBookController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\FeedBackController;


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
    ->prefix('hand-books')
    ->group(function () {
        Route::post('/', [HandBookController::class, 'store'])
            ->name('store');
        Route::patch('/{handbook}', [HandBookController::class, 'update'])
            ->name('update');
        Route::delete('/{handbook}', [HandBookController::class, 'destroy'])
            ->name('destroy');

        Route::get('/hand-books/{handbook}/download', [HandBookController::class, 'download'])
            ->name('download');
    });


Route::get('/lost-and-found', [ItemController::class, 'index'])->middleware(['auth'])->name('lost-found');


Route::middleware(['auth', 'role:admin'])
    ->name('items.')
    ->prefix('items')
    ->group(function () {
        Route::post('/', [ItemController::class, 'store'])
            ->name('store');
        Route::patch('/{item}', [ItemController::class, 'update'])
            ->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])
            ->name('destroy');
    });


Route::get('/evaluate', [FeedBackController::class, 'index'])->middleware(['auth'])->name('evalute');
Route::middleware(['auth'])
    ->name('feedback.')
    ->prefix('feedback')
    ->group(function () {
        Route::post('/', [FeedBackController::class, 'store'])
            ->name('store')->middleware('role:student');
        Route::patch('/{feedback}', [FeedBackController::class, 'update'])
            ->name('update')
            ->middleware('role:admin');
        Route::delete('/{feedback}', [FeedBackController::class, 'destroy'])
            ->name('destroy')
            ->middleware('role:admin');
    });


Route::get('/scc-officers', function () {
    return Inertia::render('ssc-officers/index', [
        'pageTitle' => 'PCNL - SCC Officers'
    ]);
})->middleware(['auth'])
    ->name('scc-officers');


Route::get('/concerns', function () {
    return Inertia::render('concerns/index', [
        'pageTitle' => 'PCNL - Concerns'
    ]);
})->middleware(['auth'])
    ->name('evaluate');
