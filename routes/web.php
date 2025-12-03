<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;


Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login'); 

Route::post('/login', [LoginController::class, 'authenticate']);

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout'); 

Route::get('/', function(){ return view('inventory'); })->middleware('auth');