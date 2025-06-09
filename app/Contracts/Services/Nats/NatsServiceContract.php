<?php

namespace App\Contracts\Services\Nats;

use App\DTO\Services\Nats\UserDTO;

interface NatsServiceContract
{
	public function sendLoginEvent(UserDTO $user): void;
}
