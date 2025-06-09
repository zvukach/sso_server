<?php

namespace App\DTO\Services\Nats;

use App\Models\User;

readonly class UserDTO
{
	public function __construct(
		public int    $id,
		public string $email,
	)
	{
	}

	public static function fromUser(User $user): self
	{
		return new self(
			$user->id,
			$user->email
		);
	}

	public function toArray(): array
	{
		return [
			'user_id' => $this->id,
			'email' => $this->email,
		];
	}
}
