<?php

namespace App\Http\Controllers\Academic\Result;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\SubjectMapping;
use App\Models\Classes;
use App\Models\Result;

class ResultController extends Controller
{
    public function index(){
      $exams = Exam::select('id as value', 'name as label')->get();
      $classes = Classes::select('id as value', 'name as label')->get();
      
      return inertia('Academic/Sheet/ClassBy', compact('exams', 'classes'));
    }
    
    public function sheet_data(Request $request){
      $exam_id = $request->exam_id;
      $class_id = $request->class_id;
      $mappings = SubjectMapping::where('exam_subject_distributions.exam_id', $exam_id)
                 ->where('exam_subject_distributions.class_id', $class_id)
                 ->join('subjects', 'exam_subject_distributions.subject_id', '=', 'subjects.id')
                 ->select([
                   'exam_subject_distributions.subject_id',
                   'exam_subject_distributions.class_id',
                   'exam_subject_distributions.exam_id',
                   'exam_subject_distributions.full_mark',
                   'exam_subject_distributions.criteria', 
                   'subjects.name'
                 ])
                 ->orderBy('subjects.id', 'ASC')
                 ->get();
      $subCriteria = [];
      foreach ($mappings as $map){
        $temp = [];
        foreach (json_decode($map->criteria, true) as $item){
          $temp[] = $item['short_title'];
        }
        $subCriteria[$map->subject_id] = [
          'full_mark' => $map->full_mark,
          'subject_name' => $map->name,
          'criteria' => $temp
        ];
      }
      
      $results = Result::join('students', 'students.id', '=', 'results.student_id')
                  ->join('subjects', 'subjects.id', '=', 'results.subject_id')
                  ->where('results.exam_id', $exam_id)
                  ->where('results.class_id', $class_id)
                  ->select([
                    'results.total_mark_obtain', 'results.point',
                    'results.grade', 'results.status', 'results.result',
                    'students.name as student_name', 'students.roll as student_roll',
                    'subjects.id as subject_id', 'subjects.name as subject_name'
                  ])
                  ->orderBy('results.subject_id', 'ASC')
                  ->orderBy('students.roll', 'ASC')
                  ->get();
      
      $result_data = [];
      foreach ($results as $result) {
          // Check if the student already exists in the result_data array
          if (!isset($result_data[$result->student_name])) {
              $result_data[$result->student_name] = [
                  'student_name' => $result->student_name,
                  'student_roll' => $result->student_roll,
                  'subjects' => []  // Initialize subjects array
              ];
          }
      
          // Add the subject information to the subjects array of the student
          $result_data[$result->student_name]['subjects'][$result->subject_name] = [
              'subject_name' => $result->subject_name,
              'point' => $result->point * 1,
              'total_mark_obtain' => $result->total_mark_obtain,
              'result' => [],
          ];
          $criteria = [];
          foreach (json_decode($result->result, true) as $crit){
            $criteria[$crit['short_title']] = [
              'mark_obtain' => $crit['mark_obtain'],
              'status' => $crit['status']
            ];
          }
          $result_data[$result->student_name]['subjects'][$result->subject_name]['result']
          =$criteria;
      }
      return [
        'head' => $subCriteria,
        'result' => $result_data
      ];
    }
}