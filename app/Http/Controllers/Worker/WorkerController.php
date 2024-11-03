<?php

namespace App\Http\Controllers\Worker;

use Exception;
use App\Models\Worker\Worker;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\Worker\StoreRequest;
use App\Http\Requests\Worker\Worker\UpdateRequest;
use App\Http\Resources\Worker\WorkerResource;

class WorkerController extends Controller
{
    use Filter;
    public function index(){
      $workers = WorkerResource::collection($this->getFilterData(Worker::class, [
        'like' => ["name", "address", "phone", "selery"]
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('Worker/Worker', compact('workers', 'params'));
    }
    
    public function store(StoreRequest $req){
      try{
        Worker::create($req->validated());
        $toast = [
          'message' => 'Worker has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('worker.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $worker = Worker::find($id);
        $worker->update($req->validated());
        $toast = [
          'message' => 'Worker <strong>'.$worker->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $worker = Worker::findOrFail($id);
        $worker->delete();
        $toast = [
          'message' => 'Worker <strong>'.$worker->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
