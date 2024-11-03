<?php

namespace App\Http\Requests\Order\Delivery;

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
        return gate_allows('update_delivery');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
                        'dref' => 'required|min:4',
            'date' => 'required|min:4',
            'product_id' => 'required|min:4',
            'quantity' => 'required|min:4',
            'destination' => 'required|min:4',
        ];
    }
}
