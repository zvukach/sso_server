<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		$this->app->bind(\App\Contracts\Repositories\User\UserRepositoryContract::class, function () {
			return new \App\Repositories\UserRepository(new \App\Models\User());
		});
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		//
	}

	public $bindings = [
		\App\Contracts\Services\Auth\AuthServiceContract::class => \App\Services\Auth\AuthService::class,
		\App\Contracts\Services\TokenStorage\TokenStorageContract::class => \App\Services\TokenStorage\RedisTokenStorage::class,
//		\App\Contracts\Services\TokenStorage\TokenStorageContract::class => \App\Services\TokenStorage\NatsKVTokenStorage::class,
		\App\Contracts\Services\Nats\NatsServiceContract::class => \App\Services\Nats\NatsService::class,
		\App\Contracts\Services\Auth\GoogleAuthServiceContract::class => \App\Services\Auth\GoogleAuthService::class
	];

	public function provides(): array
	{
		return [
			\App\Contracts\Services\Auth\AuthServiceContract::class,
			\App\Contracts\Services\TokenStorage\TokenStorageContract::class,
			\App\Contracts\Repositories\User\UserRepositoryContract::class,
			\App\Contracts\Services\Nats\NatsServiceContract::class,
			\App\Contracts\Services\Auth\GoogleAuthServiceContract::class
		];
	}
}
