<?php

namespace App\Http\Resources\Worker;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
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
        'name'         => $this->name,
        'address'      => $this->address,
        'phone'        => $this->phone,
        'selery'       => $this->selery,
        'delete_url'   => route('worker.delete', $this->id),
        'edit_url'     => route('worker.update', $this->id),
      ];
    }
}
