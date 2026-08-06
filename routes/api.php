<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\CourtImageController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TournamentController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Courts
    Route::apiResource('courts', CourtController::class);

    // Court Images
    Route::post('/court-images', [CourtImageController::class, 'store']);
    Route::get('/courts/{court}/images', [CourtImageController::class, 'index']);
    Route::delete('/court-images/{courtImage}', [CourtImageController::class, 'destroy']);

    // Time Slots
    Route::apiResource('time-slots', TimeSlotController::class);

    // User Booking APIs
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    // Wishlist APIs
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{court}', [WishlistController::class, 'destroy']);

    // Review APIs
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/courts/{court}/reviews', [ReviewController::class, 'index']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    Route::apiResource('tournaments', TournamentController::class);

    //user Tournaments APIs
    Route::post('/tournaments/{tournament}/join', [TournamentController::class, 'joinTournament']);
    Route::delete('/tournaments/{tournament}/leave', [TournamentController::class, 'leaveTournament']);
    Route::prefix('user')->group(function () {
        Route::get('/my-tournaments', [TournamentController::class, 'myJoinedTournaments']);
    });

    // Owner Booking APIs
    Route::prefix('owner')->group(function () {
        Route::get('/bookings', [BookingController::class, 'ownerBookings']);
        Route::put('/bookings/{booking}', [BookingController::class, 'update']);
        Route::get('/tournaments', [TournamentController::class, 'myTournaments']);
    });
});
