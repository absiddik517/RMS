<?php

namespace App\Http\Controllers\Academic\Result;

use Exception;
use App\Models\Exam;
use App\Models\SubjectMapping;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Helper\Traits\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\Result\Index\StoreRequest;
use App\Http\Requests\Academic\Result\Index\UpdateRequest;
use App\Http\Resources\Academic\Result\IndexResource;

class IndexController extends Controller
{
    use Filter;
    public function index(){
      $results = IndexResource::collection($this->getFilterData(Result::class, [
        'like' => ["total_mark_obtain", "point", "grade"],
        'equal' => ["student_id", "exam_id", 'class_id', "subject_id", "status"]
      ], ['student', 'exam', 'subject', 'classs'])->withQueryString());
      $params = $this->getParams();
      //dd($params);
      return inertia('Academic/Result/Index', compact('results', 'params'));
    }
    
    public function create(){
      $exams = Exam::select('id as value', 'name as label')->get();
      $classes = Classes::select('id as value', 'name as label')->get();
      return inertia('Academic/Result/Create', compact('exams', 'classes'));
    }
    
    public function store(StoreRequest $req){
      //dd($req->validated());
      try{
        foreach ($req->validated()['results'] as $data){
          $id = $data['id'];
          $result = $data['result'];
          unset($data['id']);
          unset($data['result']);
          if($id){
            Result::find($id)->update([
            ...$data, 'result' => json_encode($result)
            ]);
          }else{
            Result::create([
              ...$data, 'result' => json_encode($result)
            ]);
          }
        }
        $toast = [
          'message' => 'Result has been <kbd>saved</kbd> successful!', 
          'type' => 'success'
        ];
      } catch (Exception $e) {
        $toast = [
          'message' => $e->getMessage(), 
          'type' => 'error'
        ];
      }
      return redirect()->route('result.index')->with('toast', $toast);
    }
    
    public function update($id, UpdateRequest $req){
      try{
        $index = Result::find($id);
        $index->update($req->validated());
        $toast = [
          'message' => 'Index <strong>'.$index->name.'</strong> has <kbd>updated</kbd> successful!', 
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
        $index = Result::findOrFail($id);
        $index->delete();
        $toast = [
          'message' => 'Index <strong>'.$index->name.'</strong> has <kbd>deleted</kbd> successfull!', 
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
    public function get_subjects(Request $req){
      return Subject::where('class_id', $req->class_id)->select('id as value', 'name as label')->get();
    }
    public function get_students(Request $req){
      $subject_mapings = SubjectMapping::select('full_mark', 'criteria')
                         ->where('exam_id', $req->exam_id)
                         ->where('class_id', $req->class_id)
                         ->where('subject_id', $req->subject_id)
                         ->first();
      $results = Result::select(['id','total_mark_obtain', 'status', 'result', 'point', 'grade', 'student_id'])
                         ->where('exam_id', $req->exam_id)
                         ->where('class_id', $req->class_id)
                         ->where('subject_id', $req->subject_id)
                         ->get(); 
      $student_results = [];
      $hasResult = $results->count();
      if($hasResult){
        foreach ($results as $item) {
            $temp = [
                "id" => $item['id'],
                "total_mark_obtain" => $item['total_mark_obtain'],
                "point" => $item['point'],
                "grade" => $item['grade'],
                "status" => $item['status'],
                "result" => [],
            ];
            foreach (json_decode($item['result'], true) as $cri){
              $temp['result'][$cri['title']] = [
                "mark_obtain" => $cri["mark_obtain"],
                "status" => $cri["status"],
              ];
            }
            $student_results[$item['student_id']] = $temp;
        }
      }
      //dd($results->count(), $hasResult);
      $students = Student::select('id', 'name', 'roll')
                        ->where('class_id', $req->class_id)
                        ->orderBy('roll', 'ASC')->get();
      $output = [];
      
      foreach ($students as $st){
        $criteria = [];
        foreach (json_decode($subject_mapings->criteria, true) as $sub){
          $criteria[] = [
            'title' => $sub['title'],
            'short_title' => $sub['short_title'],
            'full_mark' => (int)$sub['full_mark'],
            'pass_mark' => (int)$sub['pass_mark'],
            'mark_obtain' => ($hasResult) ?
            $student_results[$st->id]["result"][$sub['title']]['mark_obtain'] : '',
            'status' => ($hasResult) ? $student_results[$st->id]["result"][$sub['title']]['status'] : 0,
          ];
        }
        $output[] = [
          'id' => ($hasResult) ? $student_results[$st->id]['id'] : null,
          'exam_id' => $req->exam_id,
          'class_id' => $req->class_id,
          'roll' => $st->roll,
          'student_id' => $st->id,
          'student_name' => $st->name,
          'subject_id' => $req->subject_id,
          'total_mark_obtain' => ($hasResult) ? $student_results[$st->id]['total_mark_obtain'] : 0,
          'full_mark' => $subject_mapings->full_mark,
          'point' => ($hasResult) ? $student_results[$st->id]['point'] : 0,
          'grade' => ($hasResult) ? $student_results[$st->id]['grade'] : 'F',
          'status' => ($hasResult) ? $student_results[$st->id]['status'] : 0,
          'result' => $criteria
        ];
        
      }
      
      return $output;
    }
}
