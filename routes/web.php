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
use App\Http\Controllers\VoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\FormController;
use Illuminate\Http\Request;

Route::middleware(['web'])->group(function () {
    // Public routes (accessible without authentication)
    Route::get('/login', [AuthController::class, 'index'])->middleware('guest');
    Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/forgot-password', [AuthController::class, 'forgot_password_page'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'forgot_password'])
        ->middleware('guest')
        ->name('password.email');

    Route::get('/reset-password/{token}', function (Request $request, $token) {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    })->middleware('guest')->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'reset_password'])
        ->middleware('guest')
        ->name('password.update');


    // Authenticated routes
    Route::middleware(['auth'])->group(function () {
        Route::resource('/users', UserController::class)->middleware('role:admin');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


        Route::resource('/elections', ElectionController::class)->middleware('role:admin');
        Route::resource('/forms', FormController::class)->middleware('role:admin');

        // General user routes
        Route::get('/', [AnnouncementController::class, 'index'])->name('home');
        Route::get('/lost-and-found', [ItemController::class, 'index'])->name('lost-found');
        Route::get('/evaluate', [FeedBackController::class, 'index'])->name('evaluate');
        Route::get('/scc-officers', [OfficersController::class, 'index'])->name('scc-officers');

        Route::get('/settings', function () {
            return Inertia::render('Auth/Settings', [
                'pageTitle' => 'PCNL - Settings',
                'user' => Auth::user()
            ]);
        })->name('settings');

        Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [UserController::class, 'updatePasFesword'])->name('profile.password');



        Route::get('/concerns', [ConversationController::class, 'index'])->name('concerns.index');
        Route::post('/concerns', [ConversationController::class, 'store'])->name('concerns.store');
        Route::post('/concerns/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('concerns.sendMessage');
        Route::delete('/concerns/{conversation}', [ConversationController::class, 'destroy'])->name('concerns.destroy');



        // Feedback routes
        Route::prefix('feedback')->name('feedback.')->group(function () {
            Route::post('/', [FeedBackController::class, 'store'])
                ->middleware('role:student')
                ->name('store');
            Route::patch('/{feedback}', [FeedBackController::class, 'update'])
                ->middleware('role:admin')
                ->name('update');
            Route::delete('/{feedback}', [FeedBackController::class, 'destroy'])
                ->middleware('role:admin')
                ->name('destroy');
        });

        // Voting routes
        Route::post('/votes', [VoteController::class, 'store'])->name('votes.store');
        Route::get('/votes/status', [VoteController::class, 'checkVoteStatus'])->name('votes.status');
        Route::get('/elections/{election}/results', [VoteController::class, 'results'])->name('votes.results');

        // Admin-only routes
        Route::middleware(['role:admin'])->group(function () {
            // Announcements
            Route::prefix('announcements')->name('announcements.')->group(function () {
                Route::post('/', [AnnouncementController::class, 'store'])->name('store');
                Route::patch('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
                Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
            });

            // Events
            Route::prefix('events')->name('events.')->group(function () {
                Route::post('/', [EventController::class, 'store'])->name('store');
                Route::patch('/{event}', [EventController::class, 'update'])->name('update');
                Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
            });

            // Handbooks
            Route::prefix('hand-books')->name('hand-books.')->group(function () {
                Route::post('/', [HandBookController::class, 'store'])->name('store');
                Route::patch('/{handbook}', [HandBookController::class, 'update'])->name('update');
                Route::delete('/{handbook}', [HandBookController::class, 'destroy'])->name('destroy');
                Route::get('/{handbook}/download', [HandBookController::class, 'download'])->name('download');
            });

            // Items (Lost and Found)
            Route::prefix('items')->name('items.')->group(function () {
                Route::post('/', [ItemController::class, 'store'])->name('store');
                Route::patch('/{item}', [ItemController::class, 'update'])->name('update');
                Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
            });

            // Instructors
            Route::prefix('instructor')->name('instructor.')->group(function () {
                Route::post('/', [InstructorController::class, 'store'])->name('store');
                Route::patch('/{instructor}', [InstructorController::class, 'update'])->name('update');
                Route::delete('/{instructor}', [InstructorController::class, 'destroy'])->name('destroy');
            });

            // Parties
            Route::prefix('parties')->name('parties.')->group(function () {
                Route::post('/', [PartyListController::class, 'store'])->name('store');
                Route::patch('/{parties}', [PartyListController::class, 'update'])->name('update');
                Route::delete('/{parties}', [PartyListController::class, 'destroy'])->name('destroy');
            });

            // Roles
            Route::prefix('role')->name('role.')->group(function () {
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::patch('/{role}', [RoleController::class, 'update'])->name('update');
                Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
            });

            // Elections
            Route::prefix('election')->name('election.')->group(function () {
                Route::post('/', [ElectionController::class, 'store'])->name('store');
                Route::patch('/{election}', [ElectionController::class, 'update'])->name('update');
                Route::delete('/{election}', [ElectionController::class, 'destroy'])->name('destroy');
            });

            // Candidates
            Route::prefix('candidates')->name('candidates.')->group(function () {
                Route::post('/', [CandidateController::class, 'store'])->name('store');
                Route::patch('/{candidate}', [CandidateController::class, 'update'])->name('update');
                Route::delete('/{candidate}', [CandidateController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
