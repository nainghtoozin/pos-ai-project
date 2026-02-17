<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tax.edit') ?? false;
    }

    public function rules(): array
    {
        $taxId = $this->route('tax')->id ?? null;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:taxes,name,' . $taxId],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
