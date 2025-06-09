<?php

namespace App\DTO\Requests\Auth;

readonly class RefreshDTO
{
	public function __construct(
		public string $refreshToken
	) {
	}

	public function toArray(): array
	{
		return [
			'refresh_token' => $this->refreshToken
		];
	}
}
