<?php

namespace App\DTO\Services\Auth;

use Illuminate\Support\Arr;

readonly class TokenDataDTO
{
	public function __construct(
		public string $userId,
		public string $expiresAt,
	)
	{
	}

	public static function fromArray(array $data): self
	{
		return new self(
			Arr::get($data, 'user_id'),
			Arr::get($data, 'expires_at'),
		);
	}
}
