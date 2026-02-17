<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product.edit') ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id ?? null;
        $hasBarcode = $this->boolean('has_barcode', true);

        return [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => [
                $hasBarcode ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->whereNotNull('barcode')->ignore($productId),
            ],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku,' . $productId],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'tax_id' => ['nullable', 'exists:taxes,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'has_barcode' => ['sometimes', 'boolean'],
            'sale_price' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
        ];
    }
}
