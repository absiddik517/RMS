<?php

namespace App\Http\Requests\Order\CustomerPayment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return gate_allows('update_customer_payment');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
                        'customer_id' => 'required|min:4',
            'order_ref' => 'required|min:4',
            'payment_type' => 'required|min:4',
            'description' => 'required|min:4',
            'amount' => 'required|min:4',
        ];
    }
}
