<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order\Order;
use App\Helper\Traits\HasDefault;

class Customer extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $perPage = 5;
    
    protected static $hasDefault = ['user_id'];
    
    public function user() {
      return $this->belongsTo(User::class);
    }
    public function orders() {
      return $this->belongsTo(Order::class, 'id', 'customer_id');
    }
}
