<?php

namespace App\Http\Controllers\Academic;

use Exception;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\Student\StoreRequest;
use App\Http\Requests\Academic\Student\UpdateRequest;
use App\Http\Resources\Academic\StudentResource;

class StudentController extends Controller
{
    use Filter;
    public function index(){
      $classes = Classes::select(['id as value', 'name as label'])->get();
      $students = StudentResource::collection($this->getFilterData(Student::class, [
        'like' => ["class_id", "roll", "name", "father_name", "mother_name", "address", "phone"]
      ], 'classs')->withQueryString());
      $params = $this->getParams();
      return inertia('Academic/Student', compact('students', 'params', 'classes'));
    }
    
    public function store(StoreRequest $req){
      try{
        Student::create($req->validated());
        $toast = [
          'message' => 'Student has <kbd>created</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('student.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $student = Student::find($id);
        $student->update($req->validated());
        $toast = [
          'message' => 'Student <strong>'.$student->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $student = Student::findOrFail($id);
        $student->delete();
        $toast = [
          'message' => 'Student <strong>'.$student->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    
    public function get_roll(){
      $data = Student::orderBy('id', 'desc')->first();
      if(!$data) return 1;
      return $data->roll+1;
    }
    
    public function get_students(Request $req){
      $student = Student::where(function($query) use($req){
        if($req->has('class_id')){
          $query->where('class_id', $req->class_id);
        }
      })->where(function($query) use($req){
        if($req->has('roll')){
          $query->where('roll', $req->roll);
        }
      })->where(function($query) use($req){
        if($req->has('name')){
          $query->where('name', 'like', '%'.$req->name.'%');
        }
      })
      ->select('id as value', 'name as label')
      ->get();
      return $student;
    }
}
