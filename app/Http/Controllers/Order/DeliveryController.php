<?php

namespace App\Http\Controllers\Order;

use Exception;
use App\Models\Order\Product;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Delivery\StoreRequest;
use App\Http\Requests\Order\Delivery\UpdateRequest;
use App\Http\Resources\Order\DeliveryResource;

class DeliveryController extends Controller
{
    use Filter;
    
    private function getDeliveries($order_id = null){
      $del = Delivery::query()
      ->when($order_id, function($join, $id) {
        $join->where('order_id', $id);
      })
      ->join('orders', 'orders.id', 'order_deliveries.order_id')
      ->join('customers', 'orders.customer_id', 'customers.id')
      ->join('products', 'order_deliveries.product_id', 'products.id')
      ->select([
        'order_deliveries.id', 'order_deliveries.product_id', 'order_deliveries.dref', 'order_deliveries.order_ref', 'order_deliveries.quantity', 'order_deliveries.driver', 'order_deliveries.destination', 'order_deliveries.date',
        'products.name as product_name',
        'customers.name as customer_name', 'customers.address as customer_address', 'customers.phone as customer_phone', 'customers.id as customer_id',
      ])
      ->when(request()->input('search'), function($join, $text){
        $join->where('order_deliveries.order_ref', $text)->orWhere('order_deliveries.dref', $text)->orWhere('order_deliveries.quantity', $text)->orWhere('order_deliveries.driver', 'like', "%$text%")->orWhere('order_deliveries.destination', 'like', "%$text%")
             ->orWhere('customers.name', 'like', "%$text%")
             ->orWhere('products.name', 'like', "%$text%");
      })
      ->when(request()->input('order_ref'), function($join, $text){
        $join->where('order_deliveries.order_ref', $text);
      })
      ->when(request()->input('driver'), function($join, $text){
        $join->where('order_deliveries.driver', $text);
      })
      ->when(request()->input('destination'), function($join, $text){
        $join->where('order_deliveries.destination', $text);
      })
      ->when(request()->input('customer_id'), function($join, $text){
        $join->where('customers.id', $text);
      })
      ->when(request()->input('from'), function($join, $text){
        if(request()->input('to')){
          $join->whereBetween('order_deliveries.date', [$text, request()->input('to')]);
        }
        $join->where('order_deliveries.date', $text);
      })
      ->orderBy('order_deliveries.id', 'DESC')
      ->paginate(request()->input('per_page') ?? 5);
    
      $deliveries = DeliveryResource::collection(
        $del->withQueryString()
      );
      
      return $deliveries;
    }
    
    public function index(){
      $deliveries = $this->getDeliveries();
      $params = request()->only(['search', 'per_page']);
      return inertia('Order/Delivery', compact('deliveries', 'params'));
    }
    
    public function deliveries($order_id) {
      
      $deliveries = $this->getDeliveries($order_id);
      if($deliveries->count() == 0) {
        return redirect()->back()->with('toast', [
          'type' => 'error',
          'message' => 'Product has no been deliveried yet.'
        ]);
      }
      $prods = OrderItem::where('order_items.order_id', $order_id)
                    ->join('products', 'products.id', 'order_items.product_id')
                    ->select(['products.id', 'products.name'])->get();
      $products = array();
      foreach ($prods as $product){
        $products[$product->id] = $product->name;
      }
      
      $params = request()->only(['search', 'per_page']);
      return inertia('Order/Delivery/Deliveried', compact('deliveries', 'params', 'products'));
    }
    
    public function create() {
      $products = pluckByKey(Product::select('id', 'name', 'unit')->get());
      $ref_no = request()->input('ref') ?? null;
      return inertia('Order/Delivery/Create', compact('products', 'ref_no'));
    }
    
    public function store(StoreRequest $req){
      try{
        Delivery::create($req->validated());
        $toast = [
          'message' => 'Delivery has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
        return redirect()->route('order.delivery.index')->with('toast', $toast);
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
        $delivery = Delivery::find($id);
        $delivery->update($req->validated());
        $toast = [
          'message' => 'Delivery <strong>'.$delivery->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $delivery = Delivery::findOrFail($id);
        $delivery->delete();
        $toast = [
          'message' => 'Delivery <strong>'.$delivery->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    
    // api
    public function productsByRef($ref){
      $order = Order::where('ref', $ref)
        ->with('items', 'customer')
        ->addSelect([
          'payments' => function($join){
            $join->from('customer_payments')->whereColumn('orders.customer_id', 'customer_payments.customer_id')
                 ->selectRaw('SUM(amount)')->limit(1);
          }
        ])
        ->first();
      if(!$order) return abort(404, "Order not found with ref:<b> $ref</b>");
      $orders = Order::where('customer_id', $order->customer_id)
                ->with('items')
                ->get();
                
      //dd($this->deliveryWorth($orders));
      $return_array = [
        'customer' => [
          'id' => $order->customer->id,
          'name' => $order->customer->name,
          'address' => $order->customer->address,
          'order_id' => $order->id,
          'real_due' => $this->deliveryWorth($orders) - $order->payments,
          'discount_rate' => ($order->discount * 100) / $order->sub_total,
        ]
      ];
      $order_items = [];
      foreach ($order->items as $item){
        $order_items[$item->product_id] = [
          'order_item_id' => $item->id,
          'product_id' => $item->product_id,
          'deliveryable' => $item->quantity - $item->deliveried,
          'destination' => $item->destination,
          'rate' => $item->rate + $item->transport_rate,
          'product_name' => $item->product->name,
        ];
      }
      $return_array['products'] = $order_items;
      //dd($return_array);
      return $return_array;
    }
    
    private function deliveryWorth($datum) {
      $worth = 0;
      foreach ($datum as $order) {
        $sub_total = $order->sub_total;
        $discount = $order->discount;
        $discount_rate = ($discount * 100) / $sub_total;
        foreach ($order->items as $item) {
          $delivery_worth = ($item->rate + $item->transport_rate) * $item->deliveried;
          $worth += ($delivery_worth - $delivery_worth * $discount_rate / 100);
        }
      }
      return round($worth);
    }
    
    public function getDref(){
      $delivery = Delivery::orderByDesc('id')->select('dref')->first();
      if(!$delivery) return 1;
      return $delivery->dref + 1;
    }
    
    
    public function deliverySummary(){
      $products = Product::select('name', 'id')
        ->addSelect([
          'deliveried' => function($query){
            $query->from('order_deliveries')
                ->whereColumn('products.id', 'order_deliveries.product_id')
                ->selectRaw('SUM(quantity)')->limit(1);
          }
        ])
        ->get();
      return $products;
    }
}
