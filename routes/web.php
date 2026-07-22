<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::view('/login','auth.login')->name('login');
Route::view('/register','auth.register')->name('register');
