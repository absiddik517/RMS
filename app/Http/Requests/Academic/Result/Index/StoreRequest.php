<?php

namespace App\Http\Requests\Academic\Result\Index;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
  /**
  * Determine if the user is authorized to make this request.
  *
  * @return bool
  */
  public function authorize() {
    return true;
  }

  /**
  * Get the validation rules that apply to the request.
  *
  * @return array<string, mixed>
  */
  public function rules() {
    return [
      'results.*.id' => 'nullable',
      'results.*.student_id' => 'required',
      'results.*.class_id' => 'required',
      'results.*.exam_id' => 'required',
      'results.*.subject_id' => 'required',
      'results.*.total_mark_obtain' => 'required',
      'results.*.point' => 'required',
      'results.*.grade' => 'required',
      'results.*.status' => 'required',
      'results.*.result.*.title' => 'required',
      'results.*.result.*.short_title' => 'required',
      'results.*.result.*.full_mark' => 'required',
      'results.*.result.*.mark_obtain' => 'required',
      'results.*.result.*.status' => 'required',
    ];
  }
}