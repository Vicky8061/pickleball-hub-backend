<?php

use App\Http\Controllers\Api\AdminBookingController;
use App\Http\Controllers\Api\AdminCourtController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\CourtImageController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\OwnerDashboardController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminOwnerApplicationController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminOwnerController;
use App\Http\Controllers\Api\AdminReviewController;
use App\Http\Controllers\Api\AdminTournamentController;
use App\Http\Controllers\Api\OwnerApplicationController;
use App\Http\Controllers\Api\TournamentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// =====================================================
// PUBLIC ROUTES
// =====================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// =====================================================
// AUTHENTICATED ROUTES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {

    // -------------------------------------------------
    // USER / PROFILE
    // -------------------------------------------------

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);


    // =================================================
    // COURTS
    // =================================================

    // Users can view courts
    Route::get('/courts', [CourtController::class, 'index']);
    Route::get('/courts/{court}', [CourtController::class, 'show']);


    // =================================================
    // TIME SLOTS - USER VIEW
    // =================================================

    Route::get('/time-slots', [TimeSlotController::class, 'index']);
    Route::get('/time-slots/{time_slot}', [TimeSlotController::class, 'show']);


    // =================================================
    // COURT IMAGES - USER VIEW
    // =================================================

    Route::get('/courts/{court}/images', [CourtImageController::class, 'index']);


    // =================================================
    // BOOKINGS - USER
    // =================================================

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);


    // =================================================
    // WISHLIST
    // =================================================

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{court}', [WishlistController::class, 'destroy']);


    // =================================================
    // REVIEWS
    // =================================================

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/courts/{court}/reviews', [ReviewController::class, 'index']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);


    // =================================================
    // TOURNAMENTS - USER VIEW
    // =================================================

    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/{tournament}', [TournamentController::class, 'show']);

    // User joins/leaves tournament
    Route::post(
        '/tournaments/{tournament}/join',
        [TournamentController::class, 'joinTournament']
    );

    Route::delete(
        '/tournaments/{tournament}/leave',
        [TournamentController::class, 'leaveTournament']
    );

    Route::get(
        '/user/my-tournaments',
        [TournamentController::class, 'myJoinedTournaments']
    );

    //Become Owner
    Route::post(
        '/owner/apply',
        [OwnerApplicationController::class, 'applyForOwner']
    );

    Route::get(
        '/owner/application',
        [OwnerApplicationController::class, 'myApplication']
    );


    // =================================================
    // OWNER APIs
    // =================================================

    Route::prefix('owner')
        ->middleware('owner')
        ->group(function () {

            // -----------------------------------------
            // OWNER DASHBOARD
            // -----------------------------------------

            Route::get(
                '/dashboard',
                [OwnerDashboardController::class, 'index']
            );


            // -----------------------------------------
            // OWNER BOOKINGS
            // -----------------------------------------

            Route::get(
                '/bookings',
                [BookingController::class, 'ownerBookings']
            );

            Route::put(
                '/bookings/{booking}',
                [BookingController::class, 'update']
            );


            // -----------------------------------------
            // OWNER COURTS
            // -----------------------------------------

            Route::post(
                '/courts',
                [CourtController::class, 'store']
            );

            Route::put(
                '/courts/{court}',
                [CourtController::class, 'update']
            );

            Route::patch(
                '/courts/{court}',
                [CourtController::class, 'update']
            );

            Route::delete(
                '/courts/{court}',
                [CourtController::class, 'destroy']
            );


            // -----------------------------------------
            // OWNER COURT IMAGES
            // -----------------------------------------

            Route::post(
                '/court-images',
                [CourtImageController::class, 'store']
            );

            Route::delete(
                '/court-images/{courtImage}',
                [CourtImageController::class, 'destroy']
            );


            // -----------------------------------------
            // OWNER TIME SLOTS
            // -----------------------------------------

            Route::post(
                '/time-slots',
                [TimeSlotController::class, 'store']
            );

            Route::put(
                '/time-slots/{time_slot}',
                [TimeSlotController::class, 'update']
            );

            Route::patch(
                '/time-slots/{time_slot}',
                [TimeSlotController::class, 'update']
            );

            Route::delete(
                '/time-slots/{time_slot}',
                [TimeSlotController::class, 'destroy']
            );


            // -----------------------------------------
            // OWNER TOURNAMENTS
            // -----------------------------------------

            Route::post(
                '/tournaments',
                [TournamentController::class, 'store']
            );

            Route::put(
                '/tournaments/{tournament}',
                [TournamentController::class, 'update']
            );

            Route::patch(
                '/tournaments/{tournament}',
                [TournamentController::class, 'update']
            );

            Route::delete(
                '/tournaments/{tournament}',
                [TournamentController::class, 'destroy']
            );

            Route::get(
                '/tournaments',
                [TournamentController::class, 'myTournaments']
            );
        });
});


// =====================================================
// ADMIN APIs
// =====================================================

Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {

        // ---------------------------------------------
        // ADMIN DASHBOARD
        // ---------------------------------------------

        Route::get(
            '/dashboard',
            [AdminDashboardController::class, 'index']
        );


        // ---------------------------------------------
        // USER MANAGEMENT
        // ---------------------------------------------

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::patch('/users/{user}/block', [AdminUserController::class, 'block']);


        // ---------------------------------------------
        // OWNER MANAGEMENT
        // ---------------------------------------------

        Route::apiResource(
            'owners',
            AdminOwnerController::class
        );
        Route::patch('/owners/{owner}/block', [AdminOwnerController::class, 'block']);


        // ---------------------------------------------
        // COURT MANAGEMENT
        // ---------------------------------------------

        Route::apiResource(
            'courts',
            AdminCourtController::class
        );

        Route::patch(
            '/courts/{court}/status',
            [AdminCourtController::class, 'updateStatus']
        );


        // ---------------------------------------------
        // BOOKING MANAGEMENT
        // ---------------------------------------------

        Route::apiResource(
            'bookings',
            AdminBookingController::class
        )->only([
            'index',
            'show'
        ]);


        // ---------------------------------------------
        // TOURNAMENT MANAGEMENT
        // ---------------------------------------------

        Route::apiResource(
            'tournaments',
            AdminTournamentController::class
        )->only([
            'index',
            'show',
            'update',
            'destroy'
        ]);


        // ---------------------------------------------
        // REVIEW MANAGEMENT
        // ---------------------------------------------

        Route::apiResource(
            'reviews',
            AdminReviewController::class
        )->only([
            'index',
            'show',
            'destroy'
        ]);
        // ---------------------------------------------
        // Owner Application MANAGEMENT
        // ---------------------------------------------

        Route::get(
            '/owner-applications',
            [AdminOwnerApplicationController::class, 'index']
        );
        Route::patch(
            '/owner-applications/{ownerApplication}/approved',
            [AdminOwnerApplicationController::class, 'approve']
        );
        Route::patch(
            '/owner-applications/{ownerApplication}/rejected',
            [AdminOwnerApplicationController::class, 'reject']
        );
    });
