<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClassesController extends Controller
{
    public function index(){
      return inertia('Company/Season', compact('seasons', 'params'));
    }
}
