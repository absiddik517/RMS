<?php

namespace App\Http\Controllers\Academic;

use DB;
use Exception;
use App\Models\Exam;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\SubjectMapping;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\SubjectMapping\StoreRequest;
use App\Http\Requests\Academic\SubjectMapping\UpdateRequest;
use App\Http\Resources\Academic\SubjectMappingResource;

class SubjectMappingController extends Controller
{
    use Filter;
    public function index(){
      $data =
      DB::table('exam_subject_distributions')
      ->join('exams', 'exam_subject_distributions.exam_id', '=', 'exams.id')
      ->join('subjects', 'exam_subject_distributions.subject_id', '=', 'subjects.id')
      ->join('classes', 'exam_subject_distributions.class_id', '=', 'classes.id')
        ->select([
          'exam_subject_distributions.id',
          'exam_subject_distributions.full_mark',
          'exam_subject_distributions.criteria',
          'exams.name as exam_name',
          'subjects.name as subject_name',
          'classes.name as class_name'
        ])->paginate();
      $subjectmappings = SubjectMappingResource::collection($data);
      $params = $this->getParams();
      return inertia('Academic/SubjectMapping', compact('subjectmappings',
      'params'));
    }
    
    public function create(){
      $exams = Exam::select(['id as value', 'name as label'])->get();
      $classes = Classes::select(['id as value', 'name as label'])->get();
      return inertia('Academic/CreateMapping', compact('exams', 'classes'));
    }
    
    public function store(StoreRequest $req){
      //dd($req->validated());
      try{
        $created = 0;
        $updated = 0;
        foreach ($req->validated()['mappings'] as $item){
          if($item['id']){
            SubjectMapping::find($item['id'])->update([
              'exam_id' => $item['exam_id'],
              'subject_id' => $item['subject_id'],
              'class_id' => $item['class_id'],
              'full_mark' => $item['full_mark'],
              'criteria' => json_encode($item['criteria']),
            ]);
            $updated++;
          }else{
            SubjectMapping::create([
              'exam_id' => $item['exam_id'],
              'subject_id' => $item['subject_id'],
              'class_id' => $item['class_id'],
              'full_mark' => $item['full_mark'],
              'criteria' => json_encode($item['criteria']),
            ]);
            $created++;
          }
        }
        $toast = [
          'message' => 'Operation successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('exam.map.index')->with('toast', $toast);
    }
    
    public function get_exams(){
      $exams = Exam::select(['id', 'name'])->get();
      $classes = Classes::select(['id', 'name'])->get();
      $response = [
        'exams' => $exams,
        'classes' => $classes,
      ];
      return $response;
    }
    public function get_subjects($class_id){
      $exams = Subject::select(['id', 'name'])->where('class_id', $class_id)->get();
      return $exams;
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $subjectmapping = SubjectMapping::find($id);
        $subjectmapping->update($req->validated());
        $toast = [
          'message' => 'SubjectMapping <strong>'.$subjectmapping->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $subjectmapping = SubjectMapping::findOrFail($id);
        $subjectmapping->delete();
        $toast = [
          'message' => 'SubjectMapping <strong>'.$subjectmapping->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    
    public function prepareForm(Request $req){
      $class_subjects = Subject::where('class_id', $req->class_id)->select('id', 'name')->get();
      $exam_maps = SubjectMapping::where('exam_id', $req->exam_id)->where('class_id', $req->class_id)
        ->select('id', 'subject_id', 'criteria', 'full_mark')->get();
      $output = [];
      foreach ($class_subjects as $sub){
        $output[$sub->id] = [
          'id' => null,
          'exam_id' => $req->exam_id,
          'class_id' => $req->class_id,
          'subject_id' => $sub->id,
          'name' => $sub->name,
          'full_mark' => 0,
          'criteria' => []
        ];
      }
      foreach ($exam_maps as $map){
        $output[$map->subject_id]['criteria'] = json_decode($map->criteria);
        $output[$map->subject_id]['id'] = $map->id;
        $output[$map->subject_id]['full_mark'] = $map->full_mark;
      }
      
      return $output;
    }
}
