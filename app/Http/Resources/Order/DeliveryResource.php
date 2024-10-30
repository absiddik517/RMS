<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      //return parent::toArray($request);
      return [
        'id'           => $this->id,
        'dref'         => $this->dref,
        'order_ref'    => $this->order_ref,
        'product_id'   => $this->product_id,
        'date'         => date('d-m-Y', strtotime($this->date)),
        'product'      => $this->product_name,
        'quantity'     => $this->quantity,
        'destination'  => $this->destination,
        'driver'       => $this->driver,
        'customer'     => [
          'name'       => $this->customer_name,
          'address'    => $this->customer_address,
          'phone'      => $this->customer_phone,
          'id'         => $this->customer_id,
        ],
        'delete_url'   => route('order.delivery.delete', $this->id),
        'edit_url'     => route('order.delivery.update', $this->id),
      ];
    }
}
