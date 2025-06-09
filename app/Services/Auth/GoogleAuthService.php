<?php

namespace App\Services\Auth;

use App\Contracts\Repositories\User\UserRepositoryContract;
use App\Contracts\Services\Auth\AuthServiceContract;
use App\Contracts\Services\Auth\GoogleAuthServiceContract;
use App\Contracts\Services\TokenStorage\TokenStorageContract;
use App\DTO\Requests\Auth\GoogleRequestDTO;
use App\DTO\Requests\Auth\RegisterDTO;
use App\DTO\Services\Auth\AuthResponseDTO;
use App\DTO\Services\Nats\UserDTO;
use App\Jobs\SendLoginEventJob;
use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Support\Str;

final class GoogleAuthService implements GoogleAuthServiceContract
{
	private Google_Client $client;

	public function __construct(
		private readonly AuthServiceContract $authService,
		private readonly UserRepositoryContract $userRepository,
		private readonly TokenStorageContract $tokenStorage,
	) {
		$this->client = new Google_Client();
		$this->client->setClientId(config('services.google.client_id'));
		$this->client->setClientSecret(config('services.google.client_secret'));
		$this->client->setRedirectUri(config('services.google.redirect_uri'));
	}

	public function handleCallback(GoogleRequestDTO $googleRequestDTO): AuthResponseDTO
	{
		$client = $this->getClient();

		$token = $client->fetchAccessTokenWithAuthCode($googleRequestDTO->code);
		$client->setAccessToken($token['access_token']);

		$oauth2 = new Google_Service_Oauth2($client);
		$userInfo = $oauth2->userinfo->get();

		if ($user = $this->userRepository->findByGoogleId($userInfo->getId())) {
			dispatch(new SendLoginEventJob(UserDTO::fromUser($user)));

			return $this->tokenStorage->getTokensByUser($user);
		} else {
			return $this->authService->register(new RegisterDTO($userInfo->getEmail(), Str::random(), $userInfo->getId()));
		}
	}

	public function getClient(): Google_Client
	{
		$client = $this->client;
		$client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
		$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);

		return $client;
	}
}
