<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'status' => 'required|in:ordered,pending,received',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.purchase_price' => 'required|integer|min:0',
            'lines.*.selling_price' => 'nullable|integer|min:0',
            'lines.*.discount_amount' => 'nullable|integer|min:0',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount_amount' => 'nullable|integer|min:0',
            'tax_amount' => 'nullable|integer|min:0',
            'shipping_charges' => 'nullable|integer|min:0',
            'other_charges' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'At least one product is required.',
            'lines.min' => 'At least one product is required.',
            'lines.*.product_id.required' => 'Product is required.',
            'lines.*.quantity.required' => 'Quantity is required.',
            'lines.*.purchase_price.required' => 'Purchase price is required.',
        ];
    }
}
