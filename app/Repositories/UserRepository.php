<?php

namespace App\Repositories;

use App\Contracts\Repositories\User\UserRepositoryContract;
use App\DTO\Requests\Auth\RegisterDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class UserRepository extends BaseRepository implements UserRepositoryContract
{
	public function create(RegisterDTO $registerDTO): User
	{
		return $this->getModel()->create([
			'email' => $registerDTO->email,
			'password' => Hash::make($registerDTO->password),
			'google_id' => $registerDTO->googleId
		]);
	}

	public function findByEmail(string $email): ?User
	{
		return $this->getModel()->where('email', $email)->first();
	}

	public function findByGoogleId(string $googleId): ?User
	{
		return $this->getModel()->where('google_id', $googleId)->first();
	}
}
