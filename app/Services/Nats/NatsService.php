<?php

namespace App\Services\Nats;

use App\Contracts\Services\Nats\NatsServiceContract;
use App\DTO\Services\Nats\UserDTO;
use Basis\Nats\Client;
use Basis\Nats\Configuration;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

final class NatsService implements NatsServiceContract
{
	private Client $client;

	public function __construct()
	{
		$configuration = new Configuration(
			host: config('nats.server'),
			port: config('nats.port'),
			nkey: config('nats.nkey'),
		);

		$this->client = new Client($configuration);
	}

	public function sendLoginEvent(UserDTO $user): void
	{
		try {
			$payload = serialize([
				'headers' => ['event' => 'user.login'],
				'body' => json_encode([
					'user_id' => $user->id,
					'email' => $user->email,
					'timestamp' => Date::now()->toIso8601String(),
				]),
			]);

			$this->client->publish(config('nats.subject'), $payload);

			Log::info("Sent login event to NATS", [
				'payload' => $payload,
			]);
		} catch (Throwable $e) {
			Log::error("Failed to send message to NATS: " . $e->getMessage());
		}
	}
}
