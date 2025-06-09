<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryContract
{
	public function findById(int $id): ?Model;

	public function existById(int $id): bool;

	public function getModel(): Model;
}
