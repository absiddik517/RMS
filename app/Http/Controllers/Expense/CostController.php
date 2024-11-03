<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Expense\StoreRequest;
use App\Http\Resources\ExpenceResource;

class CostController extends Controller
{
  use Helper;
  
  public function index() {
    $params = $this->getParams();
    $expenses = ExpenceResource::collection($this->prepareQuery()->paginate($params['per_page']));
    return inertia('Expense/Index')->with([
      'expenses' => $expenses,
      'available_modules' => $this->modules,
      'cost_types' => $this->full_modules,
      'params' => $params,
      'summary' => $this->getSummary(),
    ]);
  }
  
  public function create() {
    return inertia('Expense/Create')->with([
      'cost_types' => $this->full_modules,
    ]);
  }
  
  public function store(StoreRequest $request){
    try{
      (new $this->models[$request->cost_type])->create($request->validated());
      return redirect()->back()->with('toast', [
        'type' => 'success',
        'message' => "$request->cost_type added successfull."
      ]);
    }catch(\Exception $e){
      return redirect()->back()->with('toast', [
        'type' => 'error',
        'message' => exception_message($e),
      ]);
    }
  }
  
  public function form(Request $request){
    return $this->getFields();
  }
}
