<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
            'mobile_phone' => 'required|unique:users|regex:/^[0-9]{10,15}$/',
            'password' => 'required|min:6',
            'role' => 'required|in:tenant,owner',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_card_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
