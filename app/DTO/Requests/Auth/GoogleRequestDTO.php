<?php

namespace App\DTO\Requests\Auth;

use Illuminate\Support\Arr;

readonly class GoogleRequestDTO
{
	public function __construct(
		public string $code,
	) {
	}

	public static function fromArray(array $data): self
	{
		return new self(
			Arr::get($data, 'code'),
		);
	}
}
