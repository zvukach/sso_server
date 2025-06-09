<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateWithJwt
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Closure(Request): (Response) $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		// Получаем токен из заголовка
		$token = $request->bearerToken();

		if (!$token) {
			return response()->json(['error' => 'Token not provided'], 401);
		}

		try {
			// Пытаемся декодировать и проверить токен
			$user = JWTAuth::setToken($token)->authenticate();

			// Токен валиден — добавляем пользователя в запрос
			$request->attributes->add(['user' => $user]);
		} catch (TokenInvalidException $e) {
			return response()->json(['error' => 'Unauthorized'], 401);
		} catch (TokenExpiredException $e) {
			return response()->json(['error' => 'Token expired'], 401);
		} catch (JWTException $e) {
			return response()->json(['error' => 'An error occurred while decoding the token'], 401);
		}

		return $next($request);
	}
}
