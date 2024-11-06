<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use App\Models\Exam;
use App\Models\SubjectMapping;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Result;
use App\Models\Student;
use App\Models\Institute;

class MarksheetController extends Controller
{
    public function index(Request $req){
      $data = $this->process_result($req);
      return PDF::loadView('pdf.marksheet', $data)->stream('tt.pdf');
    }
    
    public function print_all_marksheet(Request $req){
      $data = $this->process_all_result($req);
      return PDF::loadView('pdf.marksheets', $data)->stream('marksheets.pdf');
      return view('pdf.marksheets', $data);
      dd($data);
    }
    
    private function process_all_result($req){
      $institute = Institute::where('language', 'bn')->select('name', 'address', 'established_at')->first();
      $exam = Exam::where('id', $req->exam_id)->select('name')->first();
      $students = Student::where('students.class_id', $req->class_id)
                ->join('classes', 'classes.id', '=', 'students.class_id')
                ->select('students.id', 'students.name', 'students.roll', 'classes.name as class')
                ->orderBy('students.roll', 'asc')
                ->get();
                
      $marks_distributions = SubjectMapping::where('exam_subject_distributions.class_id', $req->class_id)
                ->join('subjects', 'subjects.id', '=', 'exam_subject_distributions.subject_id')
                ->where('exam_subject_distributions.exam_id', $req->exam_id)
                ->select([
                  'exam_subject_distributions.full_mark',
                  'exam_subject_distributions.criteria',
                  'subjects.name', 'subjects.short_name'
                ])
                ->get();
      
      $student_result = [];
      foreach ($students as $student){
        $results = Result::join('subjects', 'subjects.id', '=', 'results.subject_id')
                  ->where('results.exam_id', $req->exam_id)
                  ->where('results.class_id', $req->class_id)
                  ->where('results.student_id', $student->id)
                  ->select([
                    'results.total_mark_obtain', 'results.point', 'results.grade',
                    'results.status', 'results.result', 'subjects.name',
                    'subjects.short_name'
                  ])
                  ->get();
        
        
        // preparing result format before putting result
        $subjects = [];
        $allCriteria = [];
        foreach ($marks_distributions as $subject){
          $critera = [];
          foreach (json_decode($subject->criteria, true) as $part){
            //dd(json_decode($results->where('name',  $subject->name)->first()->result, true));
            $critera[$part['title']] = [
              'short_title' => $part['short_title'],
              'full_mark' => $part['full_mark'],
              'pass_mark' => $part['pass_mark'],
              'mark_obtain' => $this->get_criteria_data($results->where('name', $subject->name)->first(), $part['title'], 'mark_obtain'),
              'status' => $this->get_criteria_data($results->where('name', $subject->name)->first(), $part['title'], 'status'),
            ];
            if(!in_array($part['title'], $allCriteria)){
              $allCriteria[] = $part['title'];
            }
          }
          $subjects[$subject->name] = [
            'full_mark' => $subject->full_mark,
            'short_name' => $subject->short_name,
            'total_mark_obtain' => $results->where('name', $subject->name)->first()?->total_mark_obtain ?? 'Ab',
            'grade' => $results->where('name', $subject->name)->first()?->grade ?? 'F',
            'point' => $results->where('name', $subject->name)->first()?->point ?? 0.00,
            'status' => $results->where('name', $subject->name)->first()?->status ?? 0,
            'result' => $critera,
          ];
        }
        $student_result [] = [
          'subjects' => $subjects,
          'student' => $student,
          'result' => $this->calculate_result($subjects)
        ];
      }
      return [
        'theads' => $allCriteria,
        'institute' => $institute,
        'exam' => $exam,
        'students' => $student_result
      ];
    }
    
    private function process_result($req){
      $institute = Institute::where('language', 'bn')->first();
      $exam = Exam::where('id', $req->exam_id)->select('name')->first();
      $student = Student::where('students.id', $req->student_id)
                ->join('classes', 'classes.id', '=', 'students.class_id')
                ->select('students.name', 'students.roll', 'classes.name as class')
                ->first();
                
      $marks_distributions = SubjectMapping::where('exam_subject_distributions.class_id', $req->class_id)
                ->join('subjects', 'subjects.id', '=', 'exam_subject_distributions.subject_id')
                ->where('exam_subject_distributions.exam_id', $req->exam_id)
                ->select([
                  'exam_subject_distributions.full_mark',
                  'exam_subject_distributions.criteria',
                  'subjects.name', 'subjects.short_name'
                ])
                ->get();
      
      $results = Result::join('subjects', 'subjects.id', '=', 'results.subject_id')
                ->where('results.exam_id', $req->exam_id)
                ->where('results.class_id', $req->class_id)
                ->where('results.student_id', $req->student_id)
                ->select([
                  'results.total_mark_obtain', 'results.point', 'results.grade',
                  'results.status', 'results.result', 'subjects.name',
                  'subjects.short_name'
                ])
                ->get();
      
      
      // preparing result format before putting result
      $subjects = [];
      $allCriteria = [];
      foreach ($marks_distributions as $subject){
        $critera = [];
        foreach (json_decode($subject->criteria, true) as $part){
          //dd(json_decode($results->where('name',  $subject->name)->first()->result, true));
          $critera[$part['title']] = [
            'short_title' => $part['short_title'],
            'full_mark' => $part['full_mark'],
            'pass_mark' => $part['pass_mark'],
            'mark_obtain' => $this->get_criteria_data($results->where('name', $subject->name)->first(), $part['title'], 'mark_obtain'),
            'status' => $this->get_criteria_data($results->where('name', $subject->name)->first(), $part['title'], 'status'),
          ];
          if(!in_array($part['title'], $allCriteria)){
            $allCriteria[] = $part['title'];
          }
        }
        $subjects[$subject->name] = [
          'full_mark' => $subject->full_mark,
          'short_name' => $subject->short_name,
          'total_mark_obtain' => $results->where('name', $subject->name)->first()?->total_mark_obtain ?? 'Ab',
          'grade' => $results->where('name', $subject->name)->first()?->grade ?? 'F',
          'point' => $results->where('name', $subject->name)->first()?->point ?? 0.00,
          'status' => $results->where('name', $subject->name)->first()?->status ?? 0,
          'result' => $critera,
        ];
      }
      return [
        'subjects' => $subjects,
        'theads' => $allCriteria,
        'student' => $student,
        'institute' => $institute,
        'exam' => $exam,
        'result' => $this->calculate_result($subjects)
      ];
    }
    
    private function get_criteria_data($row, String $match, String $query){
      if(!$row) return 'Ab';
      $results = json_decode($row->result, true);
      foreach ($results as $result){
        if($result['title'] == $match){
          return $result[$query];
        }
      }
      return 0;
    }
    
    private function calculate_result(Array $results){
      $total_mark = 0;
      $total_points = 0;
      $is_passed = 1;
      $total_full_mark = 0;
      foreach ($results as $result){
        $total_mark += intval($result['total_mark_obtain']);
        $total_full_mark += intval($result['full_mark']);
        $total_points += intval($result['point']);
        $is_passed *= $result['status'];
      }
      $point = 0;
      if($is_passed){
        $point = $total_points/count($results);
      }
      $output = [
        'total_full_mark' => $total_full_mark,
        'total_marks' => $total_mark,
        'point' => $point,
        'percent' => ($total_mark*100)/$total_full_mark,
        'grade' => $this->calculate_point($point),
      ];
      
      return $output;
    }
    
    private function calculate_point($point){
      if(!is_numeric($point)) return 'F';
      if($point == 5){
        return 'A+';
      }else if($point >= 4){
        return 'A';
      }else if($point >= 3.5){
        return 'A-';
      }else if($point >= 3){
        return 'B';
      }else if($point >= 2){
        return 'C';
      }else if($point >= 1){
        return 'D';
      }else{
        return 'F';
      }
    }
}
