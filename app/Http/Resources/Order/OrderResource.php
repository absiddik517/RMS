<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      // ['orders.*', 'customers.name', 'customers.address', 'customers.phone']
      //$discount_percent = 
      return [
        'discount' => $this->discount,
        'id'           => $this->id,
        'ref'          => $this->ref,
        'name'         => $this->name,
        'address'      => $this->address,
        'phone'        => $this->phone,
        'amount'       => $this->amount,
        'date'         => date('d-m-Y', strtotime($this->date)),
        'items'        => OrderItemResource::collection($this->whenLoaded('items')),
        'payments'     => CustomerPaymentResource::collection($this->whenLoaded('payments')),
        'customer_id'  => $this->customer_id,
        'note'         => $this->note,
        'due_ref'      => $this->due_ref,
        'due_date'     => $this->due_date,
        'delete_url'   => route('order.order.delete', $this->id),
        'edit_url'     => route('order.order.update', $this->id),
      ];
    }
}
