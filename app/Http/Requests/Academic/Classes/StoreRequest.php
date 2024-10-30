<?php

namespace App\Http\Requests\Academic\Classes;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'required|min:2',
            'short_name' => 'required',
            'subjects.*.name' => 'required|min:2',
            'subjects.*.short_name' => 'required',
        ];
    }
}
