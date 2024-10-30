<?php

namespace App\Http\Controllers\Company;

use Exception;
use App\Models\Company\BankAccount;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\BankAccount\StoreRequest;
use App\Http\Requests\Company\BankAccount\UpdateRequest;
use App\Http\Resources\Company\BankAccountResource;

class BankAccountController extends Controller
{
    use Filter;
    public function index(){
      $bankaccounts = BankAccountResource::collection($this->getFilterData(BankAccount::class, [
        'like' => ["bank_name", "account_number", "account_name"]
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('Company/BankAccount', compact('bankaccounts', 'params'));
    }
    
    public function store(StoreRequest $req){
      try{
        BankAccount::create($req->validated());
        $toast = [
          'message' => 'BankAccount has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('company.bank.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $bankaccount = BankAccount::find($id);
        $bankaccount->update($req->validated());
        $toast = [
          'message' => 'BankAccount <strong>'.$bankaccount->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $bankaccount = BankAccount::findOrFail($id);
        $bankaccount->delete();
        $toast = [
          'message' => 'BankAccount <strong>'.$bankaccount->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
}
