<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
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
                'title'       => $this->title,
        'start'       => $this->start,
        'end'       => $this->end,
        'delete_url' => route('company.season.delete', $this->id),
        'edit_url'   => route('company.season.update', $this->id),
      ];
    }
}
