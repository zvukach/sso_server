<?php

namespace App\Http\Requests\Auth;

use App\DTO\Requests\Auth\RefreshDTO;
use Illuminate\Foundation\Http\FormRequest;

class RefreshRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'refresh_token' => 'required|string',
		];
	}

	public function messages(): array
	{
		return [
			'refresh_token.required' => 'Refresh token is required',
			'refresh_token.string' => 'Refresh token must be a string',
		];
	}

	public function getDTO(): RefreshDTO
	{
		return new RefreshDTO($this->input('refresh_token'));
	}
}
