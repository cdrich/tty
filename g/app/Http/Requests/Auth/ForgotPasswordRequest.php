<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users',
        ];
    }
     /**
     * the data of above request
     *
     * @return array
     */
    public function data()
    {
        return [
            'email' => request()->email,
            'token' => mt_rand(100000, 999999),
            'created_at' => now()
        ];
    }
}
