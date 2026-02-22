<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:payment_methods,name,' . $this->payment_method->id,
            'type' => 'required|in:cash,bank,mobile,other',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The payment method name is required.',
            'name.unique' => 'The payment method name has already been taken.',
            'type.required' => 'The type is required.',
            'type.in' => 'Please select a valid type.',
        ];
    }
}
