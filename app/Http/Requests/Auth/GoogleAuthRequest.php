<?php

namespace App\Http\Requests\Auth;

use App\DTO\Requests\Auth\GoogleRequestDTO;
use Illuminate\Foundation\Http\FormRequest;

class GoogleAuthRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'code' => 'required|string'
		];
	}

	public function messages(): array
	{
		return [
			'code.required' => 'The code field is required.',
			'code.string' => 'The code field must be a string.',
		];
	}

	public function getDTO(): GoogleRequestDTO
	{
		return GoogleRequestDTO::fromArray($this->validated());
	}
}
