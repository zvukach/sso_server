<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], (function () {
	Route::post('/register', [Controllers\Auth\AuthController::class, 'register']);
	Route::post('/login', [Controllers\Auth\AuthController::class, 'login']);
	Route::post('/refresh', [Controllers\Auth\AuthController::class, 'refresh']);
	Route::post('/logout', [Controllers\Auth\AuthController::class, 'logout']);

	Route::prefix('google')->group(function () {
		Route::get('/redirect', [Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle']);
		Route::get('/callback', [Controllers\Auth\GoogleAuthController::class, 'handleCallback']);
	});
}));

// Роут для проверки авторизации
Route::middleware(\App\Http\Middleware\AuthenticateWithJwt::class)
	->get('protected', function () {
		return response()->json([
			'message' => 'You are authenticated!',
			'user' => request()->user(),
		]);
	});
