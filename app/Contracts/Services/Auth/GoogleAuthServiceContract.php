<?php

namespace App\Contracts\Services\Auth;

use App\DTO\Requests\Auth\GoogleRequestDTO;
use App\DTO\Services\Auth\AuthResponseDTO;
use Google_Client;

interface GoogleAuthServiceContract
{
	public function getClient(): Google_Client;

	public function handleCallback(GoogleRequestDTO $googleRequestDTO): AuthResponseDTO;
}
