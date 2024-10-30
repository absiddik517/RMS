<?php

namespace App\Http\Requests\Order\Customer;

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
        return gate_allow_any(['update_customer', 'update_order']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|min:4',
            'address' => 'required|min:4',
            'phone' => 'required|min:4',
            'email' => 'required|min:4',
        ];
    }
}
