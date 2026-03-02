<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
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
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.purchase_price' => 'required|numeric|min:0',
            'lines.*.selling_price' => 'nullable|numeric|min:0',
            'lines.*.discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ];
    }
}
