<?php

namespace App\Http\Requests\Order\CustomerPayment;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
  /**
  * Determine if the user is authorized to make this request.
  *
  * @return bool
  */
  public function authorize() {
    return gate_allows('create_customer_payment');
  }

  /**
  * Get the validation rules that apply to the request.
  *
  * @return array<string, mixed>
  */
  public function rules() {
    return [
      'customer_id' => 'required',
      'order_ref' => 'required',
      'payment_type' => 'required',
      'description' => 'required|min:4',
      'amount' => 'required',
    ];
  }
}