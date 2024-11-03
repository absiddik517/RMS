<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Traits\HasDefault;

class Order extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $perPage = 5;
    
    protected static $hasDefault = ['user_id', 'season_id', 'date'];
    
    public function items(){
      return $this->hasMany(OrderItem::class);
    }
    public function payments(){
      return $this->hasMany(CustomerPayment::class);
    }
    public function customer(){
      return $this->belongsTo(\App\Models\Customer::class);
    }
    
}
