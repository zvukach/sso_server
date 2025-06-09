<?php

namespace App\Contracts\Services\Auth;

use App\DTO\Requests\Auth\LoginDTO;
use App\DTO\Requests\Auth\RefreshDTO;
use App\DTO\Requests\Auth\RegisterDTO;
use App\DTO\Services\Auth\AuthResponseDTO;

interface AuthServiceContract
{
	public function register(RegisterDTO $registerDTO): AuthResponseDTO;

	public function login(LoginDTO $loginDTO): AuthResponseDTO;

	public function logout(string $refreshToken): void;

	public function refresh(RefreshDTO $refreshDTO): AuthResponseDTO;
}
