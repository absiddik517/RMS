<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
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
        'designation'       => $this->designation,
        'selery'       => $this->selery,
        'delete_url' => route('staff.delete', $this->id),
        'edit_url'   => route('staff.update', $this->id),
      ];
    }
}
