<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\Auth\AuthServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\TokenStorage\NatsKVTokenStorage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
	public function __construct(
		protected readonly AuthServiceContract $authService
	) {
	}

	public function login(LoginRequest $request): JsonResponse
	{
		try {
			$authResponseDTO = $this->authService->login($request->getDTO());

			return response()->json([
				'token_type' => 'Bearer',
				'access_token' => $authResponseDTO->token,
				'refresh_token' => $authResponseDTO->refreshToken,
				'expires_in' => $authResponseDTO->expiresAt,
			]);
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], $e->getCode());
		}
	}

	public function register(RegisterRequest $request): JsonResponse
	{
		$authResponseDTO = $this->authService->register($request->getDTO());

		return response()->json([
			'token_type' => 'Bearer',
			'access_token' => $authResponseDTO->token,
			'refresh_token' => $authResponseDTO->refreshToken,
			'expires_in' => $authResponseDTO->expiresAt,
		], 201);
	}

	public function refresh(RefreshRequest $request)
	{
		try {
			$authResponseDTO = $this->authService->refresh($request->getDTO());

			return response()->json([
				'token_type' => 'Bearer',
				'access_token' => $authResponseDTO->token,
				'expires_in' => $authResponseDTO->expiresAt,
			]);
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], $e->getCode());
		}

	}

	public function logout(Request $request)
	{
		$refreshToken = $request->header('X-Refresh-Token');

		if (!$refreshToken) {
			return response()->json(['error' => 'Refresh token is required'], 400);
		}

		try {
			$this->authService->logout($refreshToken);

			return response()->json(['message' => 'Successfully logged out']);
		} catch (Exception $e) {
			return response()->json(['error' => $e->getMessage()], $e->getCode());
		}
	}
}
