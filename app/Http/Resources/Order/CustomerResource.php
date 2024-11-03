<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
        'id' => $this->id,
        'name'       => $this->name,
        'address'       => $this->address,
        'phone'       => $this->phone,
        'email'       => $this->email,
        'delete_url' => route('order.customer.delete', $this->id),
        'edit_url'   => route('order.customer.update', $this->id),
      ];
    }
}
