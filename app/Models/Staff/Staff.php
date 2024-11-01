<?php

namespace App\Models\Staff;

use App\Helper\Traits\HasDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory, HasDefault;
    
    public $table = 'staffs';
    public $guarded = [];
    protected $perPage = 5;
    protected static $hasDefault = ['user_id'];
}
