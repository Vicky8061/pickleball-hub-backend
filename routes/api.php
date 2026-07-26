<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\CourtImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register',[AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user',function(Request $request){
        return $request->user;
    });
    Route::post('/logout',[AuthController::class,'logout']);
    Route::get('/profile',[AuthController::class,'profile']);
    Route::apiResource('courts', CourtController::class);

    Route::post('/court-images',[CourtImageController::class,'store']);
    Route::get('/courts/{court}/images',[CourtImageController::class,'index']);
    Route::delete('/court-images/{courtImage}',[CourtImageController::class,'destroy']);
});
