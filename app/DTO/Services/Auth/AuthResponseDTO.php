<?php

namespace App\DTO\Services\Auth;

readonly class AuthResponseDTO
{
	public function __construct(
		public string $token,
		public string $refreshToken,
		public int    $expiresAt
	) {
	}

	public function toArray(): array
	{
		return [
			'token' => $this->token,
			'refreshToken' => $this->refreshToken,
			'expiresAt' => $this->expiresAt
		];
	}
}
