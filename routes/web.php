<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;


Route::inertia('/login', 'Auth/Login')->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', function () {
    return Inertia::render('Home', [
        'pageTitle' => 'PCNL - Dashboard'
    ]);
})->middleware(['auth'])
    ->name('home');



Route::get('/evaluate', function () {
    return Inertia::render('Evaluate', [
        'pageTitle' => 'PCNL - Evaluate'
    ]);
})->middleware(['auth'])
    ->name('evaluate');



Route::get('/scc-officers', function () {
    return Inertia::render('SSCOfficer', [
        'pageTitle' => 'PCNL - SCC Officers'
    ]);
})->middleware(['auth'])
    ->name('evaluate');


Route::get('/concerns', function () {
    return Inertia::render('Concerns', [
        'pageTitle' => 'PCNL - Concerns'
    ]);
})->middleware(['auth'])
    ->name('evaluate');



Route::get('/lost-found', function () {
    return Inertia::render('LostAndFound', [
        'pageTitle' => 'PNCL - Lost & Found'
    ]);
})->middleware(['auth'])
    ->name('evaluate');
