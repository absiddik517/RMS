<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff\StaffPayment;
use App\Http\Resources\Staff\PaymentResource;

class StaffController extends Controller
{
    public function payments(){
      $payments = StaffPayment::join('staffs', 'staffs.id', 'staff_payments.staff_id')
      ->when(request()->input('staff_id'), function($query, $staff_id){
        $query->where('staff_id', $staff_id);
      })
      ->when(request()->input('search'), function($query, $text){
        $query->where('staffs.name', 'like', "%$text%")
              ->orWhere('staff_payments.description', 'like', "%$text%")
              ->orWhere('staff_payments.amount', 'like', "%$text%");
      })
      ->select([
        'staff_payments.id', 'staff_payments.description', 'staff_payments.amount', 'staff_payments.date',
        'staffs.name'
      ])
      ->paginate(request()->input('per_page') ?? 5);
      
      return PaymentResource::collection($payments);
    }
}
