<?php

namespace App\Http\Requests\Academic\Student;

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
      'class_id' => 'required',
      'roll' => 'required',
      'name' => 'required|min:4',
      'father_name' => 'nullable',
      'mother_name' => 'nullable',
      'address' => 'nullable',
      'phone' => 'nullable',
    ];
  }
}