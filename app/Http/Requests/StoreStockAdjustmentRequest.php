<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjustment_date' => ['required', 'date'],
            'type' => ['required', 'in:increase,decrease'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'integer', 'min:0'],
            'items.*.reason' => ['required', 'string', 'in:damage,expired,lost,found,correction,other'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one product is required.',
            'items.min' => 'At least one product is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.reason.required' => 'Reason is required for each product.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                if ($type === 'increase' && empty($item['unit_cost'])) {
                    $validator->errors()->add(
                        "items.{$index}.unit_cost",
                        'Unit cost is required for stock increase.'
                    );
                }
            }
        });
    }
}
