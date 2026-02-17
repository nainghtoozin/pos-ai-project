<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('sku') && $this->filled('name')) {
            $prefix = strtoupper(Str::slug($this->input('name'), ''));
            $this->merge([
                'sku' => $prefix . Str::upper(Str::random(4)),
            ]);
        }
    }

    public function rules(): array
    {
        $hasBarcode = $this->boolean('has_barcode', true);

        return [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => [
                $hasBarcode ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->whereNotNull('barcode'),
            ],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'has_barcode' => ['sometimes', 'boolean'],
            'sale_price' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'decimal:0,4'],
            'opening_stock' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
