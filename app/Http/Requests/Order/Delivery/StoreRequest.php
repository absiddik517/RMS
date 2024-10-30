<?php

namespace App\Http\Requests\Order\Delivery;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Order\DeliveryQuantity;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return gate_allows('create_delivery');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'dref'         => 'required|unique:order_deliveries|integer',
            'order_ref'    => 'required|integer',
            'order_id'     => 'required|integer',
            'product_id'   => 'required|integer',
            'quantity'     => ['required', 'integer', new DeliveryQuantity],
            'destination'  => 'required',
            'driver'       => 'required',
            'order_item_id' => 'required'
        ];
    }
}
