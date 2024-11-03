<?php

namespace App\Http\Requests\Order\Order;

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
        return gate_allows('update_order');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
                        'ref' => 'required|min:4',
            'customer_id' => 'required|min:4',
            'amount' => 'required|min:4',
            'note' => 'required|min:4',
            'due_ref' => 'required|min:4',
            'due_date' => 'required|min:4',
        ];
    }
}
