<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'address' => 'nullable|string',
            'social_profile' => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
            'advance_balance' => 'nullable|numeric|min:0',
            'township' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Supplier name is required.',
            'mobile.required' => 'Mobile number is required.',
        ];
    }
}
