<?php

namespace App\Http\Controllers\Order;

use Exception;
use App\Models\Customer;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\CustomerPayment;
use Illuminate\Support\Facades\DB;
use App\Models\Order\Product;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Order\StoreRequest;
use App\Http\Requests\Order\Order\UpdateRequest;
use App\Http\Resources\Order\OrderResource;

class OrderController extends Controller
{
    use Filter;
    public function bill() {
      $products = pluckByKey(Product::select('id', 'name', 'rate', 'transport_rate')->get()->toArray());
      return inertia('Order/Bill', compact('products'));
    }
    public function index(){
      $orders = Order::with(['items', 'payments'])->join('customers', 'orders.customer_id', 'customers.id')
              ->when(request()->input('search'), function($join, $text) {
                $join->where('orders.id', $text)
                    ->orWhere('orders.ref', 'like', "$text%")
                    ->orWhere('orders.date', $text)
                    ->orWhere('customers.name', 'like', "%$text%")
                    ->orWhere('customers.address', 'like', "%$text%")
                    ->orWhere('customers.phone', 'like', "%$text%");
              })
              ->when(request()->input('due_ref'), function($join, $text){
                $join->where('orders.due_ref', 'like', "%$text%");
              })
              ->when(request()->has('has_due'), function($join) {
                $join->where('orders.amount', '>', function($query){
                  $query->from('customer_payments')->whereColumn('customer_payments.customer_id', 'orders.customer_id')
                        ->selectRaw('sum(amount)');
                });
              })
              ->select(['orders.*', 'customers.name', 'customers.address', 'customers.phone'])->paginate(request()->input('per_page') ?? 5);
      //$orders = OrderResource::collection($orders);
      //return $orders;
      $products = Product::select('id', 'name', 'rate', 'transport_rate')->get()->toArray();
      $products = pluckByKey($products);
      $params = request()->only(['search', 'due_ref', 'has_due', 'per_page']);
      return inertia('Order/Order', compact('orders', 'params', 'products'));
    }
    public function create(){
      $products = pluckByKey(Product::select('id', 'name', 'rate', 'transport_rate')->get()->toArray());
      return inertia('Order/Create', compact('products'));
    }
    
    public function store(StoreRequest $req){
      try{
        DB::transaction(function () use($req) {
          $validated = $req->validated();
          $items = $validated['items']; unset($validated['items']);
          $customer = $validated['customer']; unset($validated['customer']);
          $payment = $validated['payment']; unset($validated['payment']);
          if($req->new_customer) {
            $customer = Customer::create($customer);
            $validated['customer_id'] = $customer->id;
          }
          
          $order = Order::create($validated);
          foreach ($items as $item){
             OrderItem::create([
               'customer_id' => $customer->id ?? $req->customer_id,
               'order_id' => $order->id,
               ...$item
              ]);
          }
          
          if($payment['amount'] > 0) {
            $payment = CustomerPayment::create([
              'customer_id' => $validated['customer_id'],
              'order_id' => $order->id,
              'description' => 'CASH ON MEMO',
              'amount' => $payment['amount']
            ]);
          }
        });
        
        $toast = [
          'message' => 'Order has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
        return redirect()->route('order.order.index')->with('toast', $toast);
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
        return redirect()->back()->with('toast', $toast);
      }
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $order = Order::find($id);
        $order->update($req->validated());
        $toast = [
          'message' => 'Order <strong>'.$order->name.'</strong> has <kbd>updated</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => exception_message($e), 
          'type' => 'error'
        ];
      }
      return redirect()->back()->with('toast', $toast);
    }
    
    public function destroy($id){
      //sleep(5);
      try{
        $order = Order::findOrFail($id);
        $order->delete();
        $toast = [
          'message' => 'Order <strong>'.$order->name.'</strong> has <kbd>deleted</kbd> successfull!', 
          'type' => 'success'
        ];
      }catch(\Exception $e){
        $toast = [
          'message' => exception_message($e), 
          'type' => 'error'
        ];
      }
      return redirect()->back()->with('toast', $toast);
    }
    
    public function getRef() {
      $data = Order::select('ref')->orderByDesc('id')->first();
      if(!$data) return 1;
      return $data->ref + 1;
    }
    
    public function OrderSummary(){
      $products = Product::select('name', 'id')
        ->addSelect([
          'total_sold' => function($query){
            $query->from('order_items')->whereColumn('products.id', 'order_items.product_id')
                  ->selectRaw('SUM(quantity)')->limit(1);
          }
        ])
        ->get();
      $order = Order::select('id')
        ->addSelect([
          'total_amount' => function($query){
            $query->from('orders')->selectRaw('SUM(amount)')->limit(1);
          },
          'total_discount' => function($query){
            $query->from('orders')->selectRaw('SUM(discount)')->limit(1);
          },
          'total_paid' => function($query){
            $query->from('customer_payments')->selectRaw('SUM(amount)')->limit(1);
          },
        ])->first();
      
      $return_array = [
        "products" => $products,
        "orders" => $order
      ];
      return $return_array;
    }
}
