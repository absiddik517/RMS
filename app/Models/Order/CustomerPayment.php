<?php

namespace App\Models\Order;

use App\Helper\Traits\HasDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $perPage = 5;
    protected $hidden = ['created_at', 'updated_at', 'user_id', 'season_id'];
    protected static $hasDefault = ['user_id', 'season_id', 'date'];
    
    public static function payment_types($id = null){
      $payment_types = collect([
        ['value' => 1, 'label' => 'Paid sell'],
        ['value' => 2, 'label' => 'Due payment']
      ]);
      if($id){
        return $payment_types->where('value', $id)->first()['label'];
      }
      return $payment_types;
    }
    
    public function customer(){
      return $this->belongsTo(\App\Models\Customer::class);
    }
}
