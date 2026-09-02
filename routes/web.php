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

        // Become Court Owner Application
        Route::get(
            '/become-owner',
            function () {
                return view('user.become_owner');
            }
        )->name('become-owner');
    });

// =====================================================
// OWNER ROUTES
// =====================================================

Route::middleware('owner.session')
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('owner.dashboard');
        })->name('dashboard');

        Route::get('/courts', function () {
            return view('owner.courts');
        })->name('courts');

        Route::get('/time-slots', function () {
            return view('owner.time_slots');
        })->name('time-slots');

        Route::get('/bookings', function () {
            return view('owner.bookings');
        })->name('bookings');

        Route::get('/tournaments', function () {
            return view('owner.tournaments');
        })->name('tournaments');

        Route::get('/earnings', function () {
            return view('owner.earnings');
        })->name('earnings');

        Route::get('/reviews', function () {
            return view('owner.reviews');
        })->name('reviews');

        Route::get('/profile', function () {
            return view('owner.profile');
        })->name('profile');

    });


// =====================================================
// ADMIN ROUTES
// =====================================================

Route::middleware('admin.session')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/owner-applications', function () {
            return view('admin.owner_applications');
        })->name('owner-applications');

        Route::get('/courts', function () {
            return view('admin.courts');
        })->name('courts');

        Route::get('/users', function () {
            return view('admin.users');
        })->name('users');

        Route::get('/banners', function () {
            return view('admin.banners');
        })->name('banners');

        Route::get('/bookings', function () {
            return view('admin.bookings');
        })->name('bookings');

        Route::get('/payouts', function () {
            return view('admin.payouts');
        })->name('payouts');

        Route::get('/reviews', function () {
            return view('admin.reviews');
        })->name('reviews');

    });


// =====================================================
// ROOT
// =====================================================

Route::get('/', function () {

    return redirect()->route('login');
});
