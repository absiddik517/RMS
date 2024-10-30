<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Order\CustomerPayment;

class CustomerPaymentResource extends JsonResource
{
  /**
  * Transform the resource into an array.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
  */
  public function toArray($request) {
  
    return [
      'id' => $this->id,
      'customer' => CustomerResource::make($this->whenLoaded('customer')),
      'order_ref' => $this->order_ref,
      'payment_type' => CustomerPayment::payment_types($this->payment_type),
      'description' => $this->description,
      'amount' => $this->amount,
      'delete_url' => route('order.customer.payment.delete', $this->id),
      'edit_url' => route('order.customer.payment.update', $this->id),
    ];
  }
}