<?php

namespace App\Http\Controllers\Order;

use Exception;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Order\Order;
use App\Models\Order\CustomerPayment;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Customer\StoreRequest;
use App\Http\Requests\Order\Customer\UpdateRequest;
use App\Http\Resources\Order\CustomerResource;

class CustomerController extends Controller
{
    use Filter;
    public function index(){
      $customers = CustomerResource::collection($this->getFilterData(Customer::class, [
        'like' => ["name", "address", "phone", "email"]
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('Order/Customer/Index', compact('customers', 'params'));
    }
    
    public function store(StoreRequest $req){
      try{
        Customer::create($req->validated());
        $toast = [
          'message' => 'Customer has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('order.customer.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $customer = Customer::find($id);
        $customer->update($req->validated());
        $toast = [
          'message' => 'Customer <strong>'.$customer->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $customer = Customer::findOrFail($id);
        $customer->delete();
        $toast = [
          'message' => 'Customer <strong>'.$customer->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    
    public function customerRef(){
      request()->validate([
        'customer_id' => 'required|integer'
      ]);
      $orders = Order::query()
        ->where('customer_id', request()->input('customer_id'))
        ->select('ref')->get()
        ->map(function($row){
          return [
            'label' => $row->ref, 
            'value' => $row->ref, 
          ];
        });
      
      return $orders;
    }
    
    public function trades($customer_id){
      $data = Customer::where('id', $customer_id)->with('user')->orderBy('id', 'ASC')->first();
      $customer = [
        'name' => $data->name,
        'address' => $data->address,
        'phone' => $data->phone,
        'email' => $data->email,
        'status' => $data->status ? 'Active' : 'Closed',
        'created_at' => date('d M Y h:i A', strtotime($data->created_at)),
      ];
      
      $trades = Order::where('customer_id', $customer_id)->with('items')->get();
      //dd($this->tradesResources($trades));
      $payments = CustomerPayment::where('customer_id', $customer_id)->select('description', 'amount', 'date')->get();
      return inertia('Order/Customer/Trade', [
        'customer' => $customer,
        'trades' => $this->tradesResources($trades),
        'payments' => $payments
      ]);
    }
    
    private function tradesResources($trades){
      $output = [];
      foreach ($trades as $trade){
        $record = [
          'date' => date('d-m-Y', strtotime($trade->date)),
          'ref' => $trade->ref,
          'sub_total' => $trade->sub_total,
          'discount' => $trade->discount,
          'amount' => $trade->amount,
        ];
        foreach ($trade->items as $item) {
          $record['items'][] = [
            'name' => $item->product->name,
            'quantity' => $item->quantity,
            'rate' => $item->rate,
            'transport_rate' => $item->transport_rate,
            'product_price' => $item->product_price,
            'transport' => $item->transport,
            'amount' => $item->amount,
            'deliveried' => $item->deliveried,
          ];
        }
        $output[] = $record;
      }
      return $output;
    }
    
    public function filterCustomer() {
      $customer = Customer::query()
          ->when(request()->input('name'), function($join, $name) {
            $join->where('name', 'like', "%$name%");
          })
          ->when(request()->input('address'), function($join, $address) {
            $join->where('address', 'like', "%$address%");
          })
          ->when(request()->input('phone'), function($join, $phone) {
            $join->where('phone', 'like',  "%$phone%");
          })
          ->when(request()->input('email'), function($join, $email) {
            $join->where('email', 'like', "%$email%");
          })
          ->select('name', 'address', 'phone', 'email', 'id')
          ->withCount('orders')
          ->get();
        if($customer->count() > 1){
          return response()->json($customer, 501);
        }
        return response()->json($customer, 501);
    }
    
    public function getOrders($customer_id) {
      $orders = Order::where('customer_id', $customer_id)->with('items')->orderBy('id', 'desc')->get();
      $response = [];
      foreach ($orders as $order) {
        $record = [
          'date' => date('d-m-Y', strtotime($order->date)),
          'ref' => $order->ref,
          'id' => $order->id,
        ];
        foreach ($order->items as $item) {
          $record['items'][] = [
            'name' => $item->product->name,
            'quantity' => $item->quantity,
            'deliveried' => $item->deliveried
          ];
        }
        $response[] = $record;
      }
      return $response;
    }
}
