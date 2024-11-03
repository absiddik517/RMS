<?php

namespace App\Models;

use App\Helper\Traits\HasDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $perPage = 5;
    //protected static $hasDefault = ['season_id', 'user_id', 'date'];
    
    public function classs()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
