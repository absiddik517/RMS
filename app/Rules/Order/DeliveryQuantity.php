<?php

namespace App\Rules\Order;

use Illuminate\Contracts\Validation\Rule;

class DeliveryQuantity implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $order;
    private $deliveryable;
    public function __construct()
    {
      $this->order = \App\Models\Order\OrderItem::where('id', request()->input('order_item_id'))->first();
      if($this->order){
        $this->deliveryable = $this->order->quantity - $this->order->deliveried;
      }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
      if(!$this->order) return false;
      return $this->deliveryable >= $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute can not more than $this->deliveryable";
    }
}
