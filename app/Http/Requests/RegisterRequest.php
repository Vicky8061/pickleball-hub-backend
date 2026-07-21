<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email'=> 'required|email|unique:users,email',
            'password'=>'required|string|min:8|confirmed',
            'role'=>'required|in:user,owner,'
        ];
    }
    public function messages():array{
        return [
            'name.required'=> 'Name is required',
            'email.required'=>'Email is required',
            'email.email'=>'Email must be a valid email address',
            'email.unique'=>'Email already exists',
            'password.required'=>'Password is required',
            'password.min'=>'Password must be at least 8 characters',
            'password.confirmed'=>'Password confirmation does not match',
            'role.required'=>'Role is required',
            'role.in'=>'Invalid role Selected',
        ];
    }
}
