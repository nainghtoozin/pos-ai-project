<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:units,name',
            'short_name' => 'required|string|max:50',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The unit name is required.',
            'name.unique' => 'The unit name has already been taken.',
            'short_name.required' => 'The short name is required.',
            'status.required' => 'The status is required.',
            'status.in' => 'Please select a valid status.',
        ];
    }
}
