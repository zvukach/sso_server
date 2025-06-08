<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/ping', function () {
	return response()->json(['status' => 'ok']);
});

Route::group(['prefix' => 'auth'], (function () {
	Route::post('/register', [AuthController::class, 'register']);
	Route::post('/login', [AuthController::class, 'login']);
	Route::post('/refresh', [AuthController::class, 'refresh']);
	Route::post('/logout', [AuthController::class, 'logout']);
}));
