<?php
namespace App\Services\TokenStorage;

use App\Contracts\Services\TokenStorage\TokenStorageContract;
use App\DTO\Services\Auth\AuthResponseDTO;
use App\DTO\Services\Auth\TokenDataDTO;
use App\Models\User;
use App\Repositories\UserRepository;
use Basis\Nats\Client;
use Basis\Nats\Configuration;
use Basis\Nats\Stream\Stream;
use Exception;
use Illuminate\Support\Str;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

final class NatsKVTokenStorage implements TokenStorageContract
{
	private Client $client;
	private Stream $stream;

	public function __construct()
	{
		$configuration = new Configuration(
			host: config('nats.server'),
			port: config('nats.port'),
			nkey: config('nats.nkey'),
		);

		$this->client = new Client($configuration);
		$this->stream = $this->client->getApi()->getStream(config('nats.stream'));
	}
	public function getTokensByUser(User $user): AuthResponseDTO
	{
		$token = $this->getAuthTokenByUser($user);
		$refreshToken = $this->getRefreshTokenUser($user);

		return new AuthResponseDTO($token, $refreshToken, config('jwt.ttl') * 60);
	}

	public function getAuthTokenByUser(User $user): string
	{
		return JWTAuth::fromUser($user);
	}

	public function getRefreshTokenUser(User $user): string
	{
		$refreshTTL = config('jwt.refresh_ttl');

		$existingTokenKey = self::getExistingTokenKey($user);
		$existingToken = $this->get($existingTokenKey);

		if ($existingToken) {
			$this->put(
				$existingTokenKey,
				'update_token',
				[
					'access_token' => $existingToken,
					'expires_at' => now()->addDays(7)->timestamp,
					'ttl' => $refreshTTL
				]);
			$this->put(
				self::getRefreshTokenKey($existingToken),
				'update_token',
				[
					'refresh_token' => $this->get(self::getRefreshTokenKey($existingToken)),
					'expires_at' => now()->addDays(7)->timestamp,
					'ttl' => $refreshTTL
				]);

			return $existingToken;
		} else {
			$refreshToken = Str::uuid()->toString();

			$data = [
				'user_id' => $user->id,
				'expires_at' => now()->addDays(7)->timestamp,
			];

			$this->put(self::getRefreshTokenKey($refreshToken), 'create_token', $data);
			$this->put($existingTokenKey, $refreshToken, $refreshTTL);

			return $refreshToken;
		}
	}

	/**
	 * @throws Exception
	 */
	public function getUserByToken(string $refreshToken): User
	{
		// Проверяем, есть ли токен в чёрном списке
		if ($this->exists(self::getBlacklistKey($refreshToken))) {
			throw new Exception('Token is blacklisted', 401);
		}

		// Проверяем, есть ли токен в Nats KV
		if (!$this->exists(self::getRefreshTokenKey($refreshToken))) {
			throw new Exception('Invalid refresh token', 401);
		}

		$tokenData = $this->getTokenData($refreshToken);
		$user = app(UserRepository::class)->findById($tokenData->userId);

		// Проверяем, актуален ли токен
		if (!$user || $tokenData->expiresAt < now()->timestamp) {
			throw new Exception('User not found or token expired', 401);
		}

		return $user;
	}

	public function destroyByRefreshToken(string $refreshToken): void
	{
		if (config('jwt.blacklist_enabled')) {
			$this->put(self::getBlacklistKey($refreshToken), 'blacklisted', config('jwt.refresh_ttl'));
		}
	}

	public function clearExpiredTokens(): int
	{
		// Nats KV не поддерживает массовое сканирование ключей
		// Если используется TTL — очистка происходит автоматически

		return 0;
	}

	private static function prepareMessageToTopic(string $eventName, array $data): string
	{
		return serialize([
			'header' => ['event' => $eventName],
			'body' => json_encode($data),
		]);
	}

	private static function getRefreshTokenKey(string $refreshToken): string
	{
		return "refresh_token:$refreshToken";
	}

	private static function getExistingTokenKey(User $user): string
	{
		return "user:{$user->id}:refresh_token";
	}

	private static function getBlacklistKey(string $refreshToken): string
	{
		return "blacklisted_tokens:$refreshToken";
	}

	private function getTokenData(string $refreshToken): TokenDataDTO
	{
		$tokenData = $this->get(self::getRefreshTokenKey($refreshToken));
		return TokenDataDTO::fromArray(json_decode($tokenData, true));
	}

	private function get(string $key): ?string
	{
		//TODO
		return null;
	}

	private function put(string $key, string $eventName, array $data): void
	{
		$value = self::prepareMessageToTopic($eventName, $data);

		$this->stream->put($key, $value);
	}

	private function exists(string $key): bool
	{
		// TODO
		return false;
	}
}
