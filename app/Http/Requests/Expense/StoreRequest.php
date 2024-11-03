<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Expense\Helper;

class StoreRequest extends FormRequest
{
  use Helper;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return gate_allows($this->user_permissions[request()->input('cost_type')]['create']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
      return $this->rules[request()->input('cost_type')];
    }
}
