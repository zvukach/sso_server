<?php

namespace App\Jobs;

use App\Contracts\Services\Nats\NatsServiceContract;
use App\DTO\Services\Nats\UserDTO;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLoginEventJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(
		protected UserDTO $userDTO
	) {
	}

	public function handle(NatsServiceContract $natsService): void
	{
		$natsService->sendLoginEvent($this->userDTO);
	}
}
