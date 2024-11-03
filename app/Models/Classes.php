<?php

namespace App\Models;

use App\Helper\Traits\HasDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $table = "classes";
    protected $perPage = 5;
    //protected static $hasDefault = ['season_id', 'user_id', 'date'];
    
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id', 'id');
    }
}
