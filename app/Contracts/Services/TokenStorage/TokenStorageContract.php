<?php

namespace App\Contracts\Services\TokenStorage;

use App\DTO\Services\Auth\AuthResponseDTO;
use App\Models\User;

interface TokenStorageContract
{
	public function getTokensByUser(User $user): AuthResponseDTO;

	public function getAuthTokenByUser(User $user): string;

	public function getRefreshTokenUser(User $user): string;

	public function getUserByToken(string $refreshToken): User;

	public function destroyByRefreshToken(string $refreshToken);

	public function clearExpiredTokens(): int;
}
