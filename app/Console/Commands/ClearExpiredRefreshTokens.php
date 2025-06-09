<?php

namespace App\Console\Commands;

use App\Contracts\Services\TokenStorage\TokenStorageContract;
use Illuminate\Console\Command;

class ClearExpiredRefreshTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка истекших Refresh Tokens';

	public function __construct(
		private readonly TokenStorageContract $tokenStorage
	) {
		parent::__construct();
	}

	public function handle(): void
	{
		$this->info('Начинаем очистку истекших Refresh Tokens...');

		$deletedCount = $this->tokenStorage->clearExpiredTokens();

		if ($deletedCount === 0) {
			$this->info('Истекших токенов не найдено.');
		} else {
			$this->info("Удалено истекших Refresh Tokens: {$deletedCount}");
		}
	}
}
