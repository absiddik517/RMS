<?php

namespace App\Http\Controllers\Academic;

use Exception;
use App\Models\Classes;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\Classes\StoreRequest;
use App\Http\Requests\Academic\Classes\UpdateRequest;
use App\Http\Resources\Academic\ClassesResource;

class ClassesController extends Controller
{
    use Filter;
    public function index(){
      $classes = ClassesResource::collection($this->getFilterData(Classes::class, [
        'like' => ["name", "short_name"], 'subjects'
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('Academic/Classes', compact('classes', 'params'));
    }
    
    public function store(StoreRequest $req){
      //dd($req->validated()['subjects']);
      try{
        Classes::create([
          "name" => $req->validated()['name'],
          "short_name" => $req->validated()['short_name'],
        ])->subjects()->createMany($req->validated()['subjects']);
        $toast = [
          'message' => 'Class has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('classes.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $class = Classes::find($id);
        $class->update([
          "name" => $req->validated()['name'],
          "short_name" => $req->validated()['short_name'],
        ]);
        //$class->subjects()->delete();
        //$class->subjects()->updateOrCreate($req->validated()['subjects']);
        foreach ($req->validated()['subjects'] as $subject){
          //dd($subject);
          if($subject['id']){
            Subject::find($subject['id'])->update([
              'class_id' => $class['id'],
              'name' => $subject['name'],
              'short_name' => $subject['short_name'],
            ]);
          }else{
            Subject::create([
              'class_id' => $class['id'],
              'name' => $subject['name'],
              'short_name' => $subject['short_name'],
            ]);
          }
        }
        
        $toast = [
          'message' => 'Class <strong>'.$class->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $class = Classes::findOrFail($id);
        $class->delete();
        $toast = [
          'message' => 'Class <strong>'.$class->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    
    public function get_classes(Request $req){
      $classes = Classes::where(function($query) use($req){
        if($req->has('name')){
          $query->where('name', 'like', '%'.$req->name.'%')
                ->where('short_name', 'like', '%'.$req->name.'%');
        }
      })->where(function($query) use($req){
        if($req->has('short_name')){
          $query->where('short_name', 'like', '%'.$req->short_name.'%');
        }
      })
      ->select('id as value', 'name as label')
      ->get();
      return $classes;
    }
}
