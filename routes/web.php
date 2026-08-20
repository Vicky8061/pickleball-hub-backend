<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// =====================================================
// AUTH
// =====================================================

Route::get('/login', function () {

    return view('auth.login');
})->name('login');


Route::get('/register', function () {

    return view('auth.register');
})->name('register');


// =====================================================
// USER DASHBOARD
// =====================================================

Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->name('user.dashboard');


// =====================================================
// OWNER DASHBOARD
// =====================================================

Route::get('/owner/dashboard', function () {

    return view('owner.dashboard');
})->name('owner.dashboard');


// =====================================================
// ADMIN DASHBOARD
// =====================================================

Route::get('/admin/dashboard', function () {

    return view('admin.dashboard');
})->name('admin.dashboard');

// =====================================================
// OPTIONAL ROOT
// =====================================================

Route::get('/', function () {
    return redirect()->route('login');
});


