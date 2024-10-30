<?php

namespace App\Http\Controllers\;

use Exception;
use App\Models\;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicClasses\StoreRequest;
use App\Http\Requests\AcademicClasses\UpdateRequest;
use App\Http\Resources\AcademicClassesResource;

class AcademicClassesController extends Controller
{
    use Filter;
    public function index(){
      $academicclasses = AcademicClassResource::collection($this->getFilterData(Models::class, [
        'like' => ["name", "short_name"]
      ])->withQueryString());
      $params = $this->getParams();
      return inertia('AcademicClasses', compact('academicclasses', 'params'));
    }
    
    public function store(StoreRequest $req){
      try{
        Models::create($req->validated());
        $toast = [
          'message' => 'AcademicClass has <kbd>created</kbd> successful!', 
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
        $academicclass = Models::find($id);
        $academicclass->update($req->validated());
        $toast = [
          'message' => 'AcademicClass <strong>'.$academicclass->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $academicclass = Models::findOrFail($id);
        $academicclass->delete();
        $toast = [
          'message' => 'AcademicClass <strong>'.$academicclass->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
