<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\Auth\GoogleAuthServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleAuthRequest;
use Exception;

class GoogleAuthController extends Controller
{
	public function __construct(
		private readonly GoogleAuthServiceContract $googleAuthService
	) {
	}

	public function redirectToGoogle()
	{
		$client = $this->googleAuthService->getClient();

		return redirect($client->createAuthUrl());
	}

	public function handleCallback(GoogleAuthRequest $request)
	{
		try {
			$authResponseDTO = $this->googleAuthService->handleCallback($request->getDTO());

			return response()->json([
				'message' => 'Logged by Google',
				'token_type' => 'Bearer',
				'access_token' => $authResponseDTO->token,
				'refresh_token' => $authResponseDTO->refreshToken,
				'expires_in' => $authResponseDTO->expiresAt,
			]);
		} catch (Exception $e) {
			return response()->json(['error' => 'Google login failed: ' . $e->getMessage()], 500);
		}
	}
}
