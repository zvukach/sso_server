<?php

namespace App\DTO\Requests\Auth;

use Illuminate\Support\Arr;

readonly class LoginDTO
{
	public function __construct(
		public string $email,
		public string $password
	) {
	}

	public static function fromArray(array $data): self
	{
		return new self(
			Arr::get($data, 'email'),
			Arr::get($data, 'password')
		);
	}

	public function toArray(): array
	{
		return [
			'email' => $this->email,
			'password' => $this->password,
		];
	}
}
