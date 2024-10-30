<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
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
                'bank_name'       => $this->bank_name,
        'account_number'       => $this->account_number,
        'account_name'       => $this->account_name,
        'delete_url' => route('company.bank.delete', $this->id),
        'edit_url'   => route('company.bank.update', $this->id),
      ];
    }
}
