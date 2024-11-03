<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Staff\PaymentStoreRequest;

class PaymentController extends Controller
{
  public function store(PaymentStoreRequest $request) {
    
    return response()->json(['hello' => 'world']);
  }
  
  public function update(Request $request) {
    return 'world';
  }
}
