<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;


Route::inertia('/login', 'Auth/Login')->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', function () {
    return Inertia::render('Home',[
        'pageTitle' => 'Home Page'
    ]);
})->middleware(['auth'])
    ->name('home');
