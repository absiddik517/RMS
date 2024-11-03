<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order\Refund;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Customer;
use App\Http\Resources\Order\RefundResource;
use App\Http\Requests\Order\Refund\StoreRequest;
use App\Http\Requests\Order\Refund\UpdateRequest;

class RefundController extends Controller
{
  public function index() {
    $refunds = RefundResource::collection(Refund::join('customers', 'refunds.customer_id', 'customers.id')
    ->join('products', 'products.id', 'refunds.product_id')
    ->select([
      'customers.name', 'customers.address', 'customers.phone',
      'refunds.quantity','refunds.amount', 'refunds.date',
      'products.name as product_name', 'products.unit'
    ])
    ->when(request()->input('search'), function($join, $text) {
      $join->where('customers.name', 'like', "%$text%")
          ->orWhere('products.name', 'like', "%$text%")
          ->orWhere('refunds.quantity', $text)
          ->orWhere('refunds.date', $text);
    })
    ->orderBy('refunds.id', 'desc')->paginate(request()->input('per_page') ?? 5)->withQueryString());
    
    $params = [
      'search' => request()->input('search') ?? null,
      'per_page' => request()->input('per_page') ?? 5,
    ];
    
    
    return inertia('Order/Refund/Index', compact('refunds', 'params'));
  }
  
  public function create() {
    return inertia('Order/Refund/Create');
  }
  
  public function store(StoreRequest $request) {
    /*
    return redirect()->back()->with('toast', [
      'type' => 'success', 
      'message' => 'Helli world'
    ]);
    */
    try {
      Refund::create($request->validated());
      return redirect()->back()->with('toast', [
        'type' => 'success',
        'message' => 'Refund store successfull!'
      ]);
    } catch (\Exception $e) {
      return redirect()->back()->with('toast', [
        'type' => 'error',
        'message' => exception_message($e)
      ]);
    }
  }
  
  public function edit() {
    return inertia('Order/Refund/Edit');
  }
  
  public function update($refund_id, UpdateRequest $request) {
    try {
      Refund::find($refund_id)->update($request->validated());
      return redirect()->back()->with('toast', [
        'type' => 'success',
        'message' => 'Refund updated successfull!'
      ]);
    } catch (\Exception $e) {
      return redirect()->back()->with('toast', [
        'type' => 'error',
        'message' => exception_message($e)
      ]);
    }
  }
  
  public function delete($refund_id) {
    try {
      Refund::find($refund_id)->delete();
      return redirect()->back()->with('toast', [
        'type' => 'success',
        'message' => 'Refund deleted successfull!'
      ]);
    } catch (\Exception $e) {
      return redirect()->back()->with('toast', [
        'type' => 'error',
        'message' => exception_message($e)
      ]);
    }
  }
  
  public function getCustomerForSelect(){
    $customer = Customer::when(request()->input('name'), function($join, $name) {
      $join->where('name', 'like', "%$name%");
    })
    ->when(request()->input('id'), function($join, $id) {
      $join->where('id', $id);
    })
    ->select('name as label', 'id as value')->limit(5)->get();
    return $customer;
  }
  
  public function getOrderForSelect(){
    $customer = Order::when(request()->input('customer_id'), function($join, $id) {
      $join->where('customer_id', $id);
    })
    ->select('ref as label', 'id as value')->limit(5)->get();
    return $customer;
  }
  public function getProductForSelect(){
    $customer = OrderItem::where('order_items.order_id', request()->input('order_id') ?? null)
    ->join('products', 'order_items.product_id', 'products.id')
    
    ->select('products.name as label', 'order_items.id', 'order_items.quantity', 'products.id as product_id', 'order_items.rate', 'order_items.transport_rate')->get();
    //return $customer;
    $data = [];
    foreach ($customer as $item){
      if($item->deliveried < $item->quantity){
        $data[] = [
          'label' => $item->label,
          'value' => $item->id,
          'refundable' => $item->quantity - $item->deliveried,
          'product_id' => $item->product_id,
          'rate' => $item->rate + $item->transport_rate,
        ];
      }
    }
    return $data;
  }
  
  
  
  
  
  
  
}
