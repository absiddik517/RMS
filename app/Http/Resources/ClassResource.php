<?php

namespace App\Http\Resources\;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
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
        'short_name'       => $this->short_name,
        'delete_url' => route('class.delete', $this->id),
        'edit_url'   => route('class.update', $this->id),
      ];
    }
}
