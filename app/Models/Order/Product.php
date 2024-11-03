<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    public $guarded = [];
    protected $perPage = 5;
    
    public function is_sold(){
      $count = OrderItem::where('product_id', $this->id)->count();
      return $count > 0;
    }
}
