<?php

namespace App\Http\Requests\Auth;

use App\DTO\Requests\Auth\RegisterDTO;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'email' => 'required|email|unique:users',
			'password' => 'required|min:6',
		];
	}

	public function messages(): array
	{
		return [
			'email.required' => 'Email is required',
			'email.email' => 'Invalid email format',
			'email.unique' => 'Email already exists',
			'password.required' => 'Password is required',
			'password.min' => 'Password must be at least 6 characters',
		];
	}

	public function getDTO(): RegisterDTO
	{
		return RegisterDTO::fromArray($this->validated());
	}
}
