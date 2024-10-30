<?php

namespace App\Http\Requests\Order\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RequiredIfExist;
use App\Rules\UniqueIf;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return gate_allows('create_order');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'ref' => 'required|integer|unique:orders',
            'customer_id' => 'required_if:new_customer,false',
            'sub_total' => 'required',
            'amount' => 'required',
            'note' => 'nullable',
            'discount' => 'nullable',
            'due_ref' => [new RequiredIfExist('due')],
            'due_date' => [new RequiredIfExist('due')],
            
            'payment.amount' => 'nullable',
            
            'customer.name' => ['required_if:new_customer,true', new UniqueIf('new_customer', 'customers', 'name')],
            'customer.address' => 'required_if:new_customer,true',
            'customer.phone' => ['required_if:new_customer,true', new UniqueIf('new_customer', 'customers', 'phone')],
            
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required',
            'items.*.rate' => 'required',
            'items.*.transport_rate' => 'nullable',
            'items.*.amount' => 'required',
            'items.*.product_price' => 'required',
            'items.*.transport' => 'nullable',
        ];
    }
    
    public function messages() {
      return [
        'ref.required' => 'Reference is required.',
        'ref.integer' => 'Reference be a number.',
        'ref.unique' => 'Reference already exist.',
        'customer_id.required_if' => 'Customer is required.',
        'sub_total.required' => 'Sub total is required.',
        
        'customer.name.required_if' => 'Name is required.',
        'customer.address.required_if' => 'Address is required.',
        'customer.phone.required_if' => 'Phone is required.',
        
        'items.*.product_id.required' => 'Product is required.',
        'items.*.quantity.required' => 'Quantity is required.',
        'items.*.rate.required' => 'Rate is required.',
        'items.*.product_price.required' => 'Product price is required.',
        'items.*.amount.required' => 'Amount is required.',
        
        
      ];
    }
}
