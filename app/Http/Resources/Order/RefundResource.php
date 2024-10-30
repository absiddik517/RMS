<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      return [
        'date' => date('d-m-Y', strtotime($this->date)),
        'quantity' => $this->quantity,
        'amount' => $this->amount,
        'customer' => [
          'name' => $this->name,
          'address' => $this->address,
          'phone' => $this->phone,
        ],
        'product' => [
          'name' => $this->product_name,
          'unit' => $this->unit,
        ]
      ];
    }
}
