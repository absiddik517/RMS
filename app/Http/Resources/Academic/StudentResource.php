<?php

namespace App\Http\Resources\Academic;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
  /**
  * Transform the resource into an array.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
  */
  public function toArray($request) {
    //return parent::toArray($request);
    return [
      'id' => $this->id,
      'class_id' => $this->class_id,
      'class_name' => $this->classs->name,
      'roll' => $this->roll,
      'name' => $this->name,
      'father_name' => $this->father_name,
      'mother_name' => $this->mother_name,
      'address' => $this->address,
      'phone' => $this->phone,
      'delete_url' => route('student.delete', $this->id),
      'edit_url' => route('student.update', $this->id),
    ];
  }
}