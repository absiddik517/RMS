<?php

namespace App\Http\Controllers\Order;

use Exception;
use App\Models\Order\CustomerPayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CustomerPayment\StoreRequest;
use App\Http\Requests\Order\CustomerPayment\UpdateRequest;
use App\Http\Resources\Order\CustomerPaymentResource;

class CustomerPaymentController extends Controller
{
    use Filter;
    
    public function index(){
      $customers = Customer::select('id','name', 'address')->get();
      $payment_types = CustomerPayment::payment_types()->values();
      $customerpayments = CustomerPaymentResource::collection(
        $this->getFilterData(
          CustomerPayment::class, 
          [
            'like' => ["customer_id", "order_ref", "payment_type", "description", "amount"]
          ],
          'customer'
        )->withQueryString());
      //dd($payment_types);
      $params = $this->getParams();
      
      return inertia('Order/CustomerPayment', compact('customerpayments', 'params', 'customers', 'payment_types'));
    }
    
    public function store(StoreRequest $req){
      try{
        CustomerPayment::create($req->validated());
        $toast = [
          'message' => 'CustomerPayment has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
        return redirect()->route('order.customer.payment.index')->with('toast', $toast);
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
        return redirect()->route('order.customer.payment.index')->with('toast', $toast)->withStatus(500);
      }
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $customerpayment = CustomerPayment::find($id);
        $customerpayment->update($req->validated());
        $toast = [
          'message' => 'CustomerPayment <strong>'.$customerpayment->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $customerpayment = CustomerPayment::findOrFail($id);
        $customerpayment->delete();
        $toast = [
          'message' => 'CustomerPayment <strong>'.$customerpayment->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    
    public function customers(){
      return Customer::when(request()->input('query'), function($join, $query){
               $join->where('name', 'like', "%$name%");
             })
             ->select('name', 'id')
             ->limit(5)->get();
    }
}
