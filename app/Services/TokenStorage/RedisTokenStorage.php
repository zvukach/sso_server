<?php

namespace App\Services\TokenStorage;

use App\Contracts\Repositories\User\UserRepositoryContract;
use App\Contracts\Services\TokenStorage\TokenStorageContract;
use App\DTO\Services\Auth\AuthResponseDTO;
use App\DTO\Services\Auth\TokenDataDTO;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Predis\Client as RedisClient;
use Tymon\JWTAuth\Facades\JWTAuth;

final class RedisTokenStorage implements TokenStorageContract
{
	private RedisClient $redis;

	public function __construct()
	{
		$this->redis = new RedisClient([
			'host' => config('database.redis.default.host'),
			'port' => config('database.redis.default.port'),
		]);
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

		// Проверяем, есть ли у пользователя уже сохранённый Refresh Token
		$existingTokenKey = self::getExistingTokenKey($user);
		$existingToken = $this->redis->get($existingTokenKey);

		if ($existingToken) { // Если есть — просто продлеваем его TTL
			$this->redis->expire($existingTokenKey, $refreshTTL);
			$this->redis->expire(self::getRefreshTokenKey($existingToken), $refreshTTL);

			return $existingToken;
		} else { // Если нет — генерируем новый
			$refreshToken = Str::uuid()->toString();

			$this->redis->setex(self::getRefreshTokenKey($refreshToken), $refreshTTL, json_encode([
				'user_id' => $user->id,
				'expires_at' => now()->addDays(7)->timestamp,
			]));

			$this->redis->setex($existingTokenKey, $refreshTTL, $refreshToken);
		}

		return $refreshToken;
	}

	/**
	 * @throws Exception
	 */
	public function getUserByToken(string $refreshToken): User
	{
		// Проверяем, есть ли токен в чёрном списке
		if (
			config('jwt.blacklist_enabled')
			&& $this->redis->exists(self::getBlacklistKey($refreshToken))
		) {
			throw new Exception('Token is blacklisted', 401);
		}

		// Проверяем, есть ли токен в Redis
		if (!$this->redis->exists(self::getRefreshTokenKey($refreshToken))) {
			throw new Exception('Invalid refresh token', 401);
		}

		$tokenDataDTO = $this->getTokenData($refreshToken);

		$user = app(UserRepositoryContract::class)->findById($tokenDataDTO->userId);

		// Проверяем, актуален ли токен
		if (!$user || $tokenDataDTO->expiresAt < now()->timestamp) {
			throw new Exception('User not found or token expired', 401);
		}

		return $user;
	}

	/**
	 * @throws Exception
	 */
	public function destroyByRefreshToken(string $refreshToken): void
	{
		if (config('jwt.blacklist_enabled')) {
			$this->redis->setex(self::getBlacklistKey($refreshToken), config('jwt.refresh_ttl'), true);
		} else {
			$user = $this->getUserByToken($refreshToken);

			$this->redis->del(self::getRefreshTokenKey($refreshToken));
			$this->redis->del(self::getExistingTokenKey($user));
		}
	}

	public function clearExpiredTokens(): int
	{
		$keys = $this->redis->keys('refresh_token:*');
		$deletedCount = 0;

		foreach ($keys as $key) {
			$data = json_decode($this->redis->get($key), true);

			if (isset($data['expires_at']) && $data['expires_at'] < now()->timestamp) {
				$this->redis->del($key);
				$deletedCount++;
			}
		}

		return $deletedCount;
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
		$tokenData = $this->redis->get(self::getRefreshTokenKey($refreshToken));

		return TokenDataDTO::fromArray(json_decode($tokenData, true));
	}
}
