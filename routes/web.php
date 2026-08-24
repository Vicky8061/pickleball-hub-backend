<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// =====================================================
// AUTH
// =====================================================

Route::get('/login', function () {

    if (session('logged_in')) {

        $user = session('auth_user');

        if ($user['role'] === 'admin') {
            return redirect('/admin/dashboard');
        }

        if ($user['role'] === 'owner') {
            return redirect('/owner/dashboard');
        }

        return redirect('/user/dashboard');
    }

    return response()
        ->view('auth.login')
        ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('login');

Route::get('/register', function () {

    return view('auth.register');
})->name('register');


// =====================================================
// CREATE SESSION AFTER API LOGIN
// =====================================================

Route::post('/auth/session', function (Request $request) {

    $request->validate([

        'user' => 'required|array',

        'user.id' => 'required|integer',

        'user.name' => 'required|string',

        'user.email' => 'required|email',

        'user.role' => 'required|string',

    ]);


    session([

        'user_id' => $request->user['id'],

        'auth_user' => $request->user,

        'logged_in' => true,

    ]);


    return response()->json([

        'success' => true,

        'message' =>
        'Session created successfully.',

    ]);
});


// =====================================================
// LOGOUT WEB SESSION
// =====================================================
Route::post('/auth/logout', function (Request $request) {

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully.',
    ]);
});


// =====================================================
// USER ROUTES
// =====================================================

Route::middleware('user.session')
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        // Dashboard
        Route::get(
            '/dashboard',
            function () {
                return view('user.dashboard');
            }
        )->name('dashboard');


        // Courts
        Route::get(
            '/courts',
            function () {
                return view('user.courts');
            }
        )->name('courts');


        // Court Details
        Route::get(
            '/courts/{id}',
            function ($id) {
                return view('user.court-details', compact('id'));
            }
        )->name('courts-details');
        
        // Booking
        Route::get(
            '/courts/{id}/book',
            function ($id) {
                return view('user.booking', compact('id'));
            }
        )->name('booking');


        // Tournaments
        Route::get(
            '/tournaments',
            function () {
                return view('user.tournaments');
            }
        )->name('tournaments');


        // Wishlist
        Route::get(
            '/wishlist',
            function () {
                return view('user.wishlist');
            }
        )->name('wishlist');


        // Bookings
        Route::get(
            '/bookings',
            function () {
                return view('user.bookings');
            }
        )->name('bookings');


        // Profile
        Route::get(
            '/profile',
            function () {
                return view('user.profile');
            }
        )->name('profile');
    });

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
// ROOT
// =====================================================

Route::get('/', function () {

    return redirect()->route('login');
});
