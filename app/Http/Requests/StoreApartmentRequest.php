<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreApartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
    if (!Auth::check()) {
        return false;
    }
    return $this->user()->isOwner();
}


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city'            => 'required|string|max:255',
            'country'         => 'required|string|max:255',
            'address'         => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'number_of_room'  => 'required|integer|min:1',
            'space'           => 'required|numeric|min:1',
            'description'     => 'required|string',
            'is_available'    => 'required|boolean',
            'images'          => 'nullable|array',
            'images.*'        => 'image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }
}
