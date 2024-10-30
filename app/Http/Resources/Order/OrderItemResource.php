<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      $total_rate = $this->rate + $this->transport_rate;
      
      return [
        'product_id'     => $this->product_id,
        'rate'           => $this->rate,
        'transport_rate' => $this->transport_rate,
        'quantity'       => $this->quantity,
        'deliveried'     => $this->deliveried,
        'product_price'  => $this->product_price,
        'transport'      => $this->transport,
        'amount'         => $this->amount,
        'destination'    => $this->destination,
      ];
    }
}
