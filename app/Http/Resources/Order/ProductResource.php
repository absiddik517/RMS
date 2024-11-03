<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
        'unit'       => $this->unit,
        'rate'       => $this->rate,
        'transport_rate'       => $this->transport_rate,
        'delete_url' => route('order.product.delete', $this->id),
        'edit_url'   => route('order.product.update', $this->id),
      ];
    }
}
