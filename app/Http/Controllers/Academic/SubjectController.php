<?php

namespace App\Http\Controllers\Academic;

use Exception;
use App\Models\Subject;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\Subject\StoreRequest;
use App\Http\Requests\Academic\Subject\UpdateRequest;
use App\Http\Resources\Academic\SubjectResource;

class SubjectController extends Controller
{
    use Filter;
    public function index(){
      $classes = Classes::select('id', 'name')->get();
      $subjects = SubjectResource::collection($this->getFilterData(Subject::class, [
        'like' => ["class_id", "name", "short_name"], 'classs'
      ])->withQueryString());
     // dd($subjects);
      $params = $this->getParams();
      return inertia('Academic/Subject', compact('subjects', 'params', 'classes'));
    }
    
    public function store(StoreRequest $req){
      try{
        Subject::create($req->validated());
        $toast = [
          'message' => 'Subject has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('subject.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $subject = Subject::find($id);
        $subject->update($req->validated());
        $toast = [
          'message' => 'Subject <strong>'.$subject->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $subject = Subject::findOrFail($id);
        $subject->delete();
        $toast = [
          'message' => 'Subject <strong>'.$subject->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
