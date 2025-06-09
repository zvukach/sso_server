<?php

namespace App\Http\Requests\Auth;

use App\DTO\Requests\Auth\LoginDTO;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'email' => 'required|email',
			'password' => 'required',
		];
	}

	public function messages(): array
	{
		return [
			'email.required' => 'Email is required',
			'email.email' => 'Invalid email format',
			'password.required' => 'Password is required',
		];
	}

	public function getDTO(): LoginDTO
	{
		return LoginDTO::fromArray($this->all());
	}
}
