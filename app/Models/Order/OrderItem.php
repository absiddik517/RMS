<?php

namespace App\Models\Order;

use App\Helper\Traits\HasDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $perPage = 5;
    protected $with = ['product', 'refunds'];
    protected static $hasDefault = ['user_id', 'season_id', 'date'];
    protected $appends = ['deliveried'];
    
    public function order() {
      return $this->belongsTo(Order::class);
    }
    
    public function product() {
      return $this->belongsTo(Product::class);
    }
    
    public function deliveries() {
      return $this->hasMany(Delivery::class);
    }
    
    public function refunds() {
      return $this->hasMany(Refund::class);
    }
    
    public function getDeliveriedAttribute()
    {
      $delivery = Delivery::where('order_item_id', $this->id)->sum('quantity');
      return $delivery;
    }
}
