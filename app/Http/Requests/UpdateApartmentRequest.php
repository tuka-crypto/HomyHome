<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApartmentRequest extends FormRequest
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
        'city' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:255',
        'price' => 'nullable|numeric',
        'number_of_room' => 'nullable|integer',
        'space' => 'nullable|integer',
        'description' => 'nullable|string|max:500',
        'is_available' => 'nullable|boolean',
    ];
    }
}
