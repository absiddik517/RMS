<?php

namespace App\Http\Requests\Order\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
  /**
  * Determine if the user is authorized to make this request.
  *
  * @return bool
  */
  public function authorize() {
    return gate_allows('update_product');
  }

  /**
  * Get the validation rules that apply to the request.
  *
  * @return array<string, mixed>
  */
  public function rules() {
    return [
      'name' => 'required|min:4',
      'unit' => 'required|min:2',
      'rate' => 'required',
      'transport_rate' => 'required',
    ];
  }
}