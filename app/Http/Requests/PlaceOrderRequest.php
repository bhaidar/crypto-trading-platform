<?php

namespace App\Http\Requests;

use App\Enums\OrderSide;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'asset_id' => ['required', 'integer', 'exists:assets,id'],
            'side' => ['required', 'string', Rule::enum(OrderSide::class)],
            'price' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
            'quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'asset_id.exists' => 'The selected asset does not exist.',
            'price.gt' => 'Price must be greater than zero.',
            'quantity.gt' => 'Quantity must be greater than zero.',
        ];
    }
}
