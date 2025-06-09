<?php

namespace App\Services\Auth;

use App\Contracts\Repositories\User\UserRepositoryContract;
use App\Contracts\Services\Auth\AuthServiceContract;
use App\Contracts\Services\TokenStorage\TokenStorageContract;
use App\DTO\Requests\Auth\LoginDTO;
use App\DTO\Requests\Auth\RefreshDTO;
use App\DTO\Requests\Auth\RegisterDTO;
use App\DTO\Services\Auth\AuthResponseDTO;
use App\DTO\Services\Nats\UserDTO;
use App\Jobs\SendLoginEventJob;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class AuthService implements AuthServiceContract
{
	public function __construct(
		private readonly UserRepositoryContract $userRepository,
		private readonly TokenStorageContract   $tokenStorage,
	) {
	}

	public function register(RegisterDTO $registerDTO): AuthResponseDTO
	{
		DB::beginTransaction();
		$user = $this->userRepository->create($registerDTO);

		dispatch(new SendLoginEventJob(UserDTO::fromUser($user)));

		$tokens = $this->tokenStorage->getTokensByUser($user);
		DB::commit();

		return $tokens;
	}

	/**
	 * @throws Exception
	 */
	public function login(LoginDTO $loginDTO): AuthResponseDTO
	{
		$user = $this->userRepository->findByEmail($loginDTO->email);

		if (!$user || !Hash::check($loginDTO->password, $user->password)) {
			throw new Exception('Invalid credentials', 401);
		}

		dispatch(new SendLoginEventJob(UserDTO::fromUser($user)));

		return $this->tokenStorage->getTokensByUser($user);
	}

	/**
	 * @throws Exception
	 */
	public function refresh(RefreshDTO $refreshDTO): AuthResponseDTO
	{
		$user = $this->tokenStorage->getUserByToken($refreshDTO->refreshToken);

		return $this->tokenStorage->getTokensByUser($user);
	}

	public function logout(string $refreshToken): void
	{
		$this->tokenStorage->destroyByRefreshToken($refreshToken);
	}
}
