<?php

namespace App\Http\Requests\Order\Refund;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
      return [
        'order_id' => 'required',
        'customer_id' => 'required',
        'order_item_id' => 'required',
        'product_id' => 'required',
        'quantity' => 'required',
        'amount' => 'required',
      ];
    }
    
    public function messages() {
      return [
        'order_id.required' => 'Select an order reference.',
        'customer_id.required' => 'Select a customer.',
        'order_item_id.required' => 'Select a product to refund.',
        'quantity.required' => 'Quantity is requird.',
        'amount.required' => 'Amount is requird.',
      ];
    }
}
