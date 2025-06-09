<?php

namespace App\Contracts\Repositories\User;

use App\Contracts\Repositories\BaseRepositoryContract;
use App\DTO\Requests\Auth\RegisterDTO;
use App\Models\User;

interface UserRepositoryContract extends BaseRepositoryContract
{
	public function findByEmail(string $email): ?User;

	public function create(RegisterDTO $registerDTO): User;

	public function findByGoogleId(string $googleId): ?User;
}
