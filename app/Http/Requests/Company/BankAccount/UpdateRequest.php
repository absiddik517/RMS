<?php

namespace App\Http\Requests\Company\BankAccount;

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
                        'bank_name' => 'required|min:4',
            'account_number' => 'required|min:4',
            'account_name' => 'required|min:4',
        ];
    }
}
