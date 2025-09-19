<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;



Route::inertia('/login', 'auth/login')->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', function () {
    return Inertia::render('dashboard/index', [
        'pageTitle' => 'PCNL - Dashboard'
    ]);
})->middleware(['auth'])
    ->name('home');



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
