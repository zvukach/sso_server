<?php

namespace App\DTO\Requests\Auth;

use Illuminate\Support\Arr;

readonly class RegisterDTO
{
	public function __construct(
		public string  $email,
		public string  $password,
		public ?string $googleId = null
	) {
	}

	public static function fromArray(array $data): self
	{
		return new self(
			Arr::get($data, 'email'),
			Arr::get($data, 'password'),
			Arr::get($data, 'google_id'),
		);
	}

	public function toArray(): array
	{
		return [
			'email' => $this->email,
			'password' => $this->password,
			'google_id' => $this->googleId
		];
	}
}
