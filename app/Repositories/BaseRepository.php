<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryContract;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryContract
{
	public function __construct(
		private readonly Model $model
	) {
	}

	public function getModel(): Model
	{
		return $this->model;
	}

	public function findById(int $id): ?Model
	{
		return $this->getModel()->find($id);
	}

	public function existById(int $id): bool
	{
		return $this->getModel()->where('id', $id)->exists();
	}
}
