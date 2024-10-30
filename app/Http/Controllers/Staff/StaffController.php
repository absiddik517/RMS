<?php

namespace App\Http\Controllers\Staff;

use Exception;
use App\Models\Staff\Staff;
use App\Models\Staff\StaffPayment;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Staff\StoreRequest;
use App\Http\Requests\Staff\Staff\UpdateRequest;
use App\Http\Resources\Staff\StaffResource;
use App\Http\Resources\Staff\PaymentResource;

class StaffController extends Controller
{
    use Filter;
    public function index(){
      $staff = StaffResource::collection($this->getFilterData(Staff::class, [
        'like' => ["name", "address", "phone", "designation", "selery"]
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('Staff/Staff', compact('staff', 'params'));
    }
    
    public function store(StoreRequest $req){
      try{
        Staff::create($req->validated());
        $toast = [
          'message' => 'Staff has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('staff.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $staff = Staff::find($id);
        $staff->update($req->validated());
        $toast = [
          'message' => 'Staff <strong>'.$staff->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $staff = Staff::findOrFail($id);
        $staff->delete();
        $toast = [
          'message' => 'Staff <strong>'.$staff->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    
    public function account($id){
      $staff = Staff::where('id', $id)->first();
      //if(!$staff) abort(404);
      return inertia('Staff/Account')->with([
        'staff' => $staff,
      ]);
    }
    
    public function seleries() {
      return 'received';
    }
    
    public function payments($id) {
      $payments = StaffPayment::join('staffs', 'staffs.id', 'staff_payments.staff_id')
      ->where('staff_id', $id)
      ->select([
        'staff_payments.id', 'staff_payments.description', 'staff_payments.amount',
        'staff.name'
      ])
      ->get();
      
      return inertia('Staff/Components/Payment')->with([
        'payments' => PaymentResource::collection($payments),
      ]);
    }
}
