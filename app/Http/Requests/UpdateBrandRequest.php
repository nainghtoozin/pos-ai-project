<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')->id ?? null;
        
        return [
            'name' => 'required|string|max:255|unique:brands,name,' . $brandId,
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brandId,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The brand name is required.',
            'name.unique' => 'This brand name already exists.',
            'status.required' => 'The status is required.',
        ];
    }
}
