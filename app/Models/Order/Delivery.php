<?php

namespace App\Models\Order;

use App\Helper\Traits\HasDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $perPage = 5;
    protected $table = "order_deliveries";
    protected static $hasDefault = ['user_id', 'season_id', 'date'];
    
    public function order() {
      return $this->belongsTo(Order::class);
    }
    
    public function product() {
      return $this->belongsTo(Product::class);
    }
}
