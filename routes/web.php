<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HandBookController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\FeedBackController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PartyListController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OfficersController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\CandidateController;

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


Route::get('/evaluate', [FeedBackController::class, 'index'])->middleware(['auth'])->name('evaluate');
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

Route::middleware(['auth', 'role:admin'])
    ->name('instructor.')
    ->prefix('instructor')
    ->group(function () {
        Route::post('/', [InstructorController::class, 'store'])
            ->name('store');
        Route::patch('/{instructor}', [InstructorController::class, 'update'])
            ->name('update');
        Route::delete('/{instructor}', [InstructorController::class, 'destroy'])
            ->name('destroy');
    });

Route::get('/scc-officers', [OfficersController::class, 'index'])->middleware(['auth'])
    ->name('scc-officers');

Route::middleware(['auth', 'role:admin'])
    ->name('parties.')
    ->prefix('parties')
    ->group(function () {
        Route::post('/', [PartyListController::class, 'store'])
            ->name('store');
        Route::patch('/{parties}', [PartyListController::class, 'update'])
            ->name('update');
        Route::delete('/{parties}', [PartyListController::class, 'destroy'])
            ->name('destroy');
    });



Route::middleware(['auth', 'role:admin'])
    ->name('role.')
    ->prefix('role')
    ->group(function () {
        Route::post('/', [RoleController::class, 'store'])
            ->name('store');
        Route::patch('/{role}', [RoleController::class, 'update'])
            ->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->name('destroy');
    });

Route::middleware(['auth', 'role:admin'])
    ->name('election.')
    ->prefix('election')
    ->group(function () {
        Route::post('/', [ElectionController::class, 'store'])
            ->name('store');
        Route::patch('/{election}', [ElectionController::class, 'update'])
            ->name('update');
        Route::delete('/{election}', [ElectionController::class, 'destroy'])
            ->name('destroy');
    });

Route::middleware(['auth', 'role:admin'])
    ->name('candidates.')
    ->prefix('candidates')
    ->group(function () {
        Route::post('/', [CandidateController::class, 'store'])
            ->name('store');
        Route::patch('/{candidate}', [CandidateController::class, 'update'])
            ->name('update');
        Route::delete('/{candidate}', [CandidateController::class, 'destroy'])
            ->name('destroy');
    });

Route::get('/concerns', function () {
    return Inertia::render('concerns/index', [
        'pageTitle' => 'PCNL - Concerns'
    ]);
})->middleware(['auth'])
    ->name('esvaluate');
