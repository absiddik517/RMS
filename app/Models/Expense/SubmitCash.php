<?php

namespace App\Models\Expense;

use App\Helper\Traits\HasDefault;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmitCash extends Model
{
    use HasFactory, HasDefault;
    
    public $guarded = [];
    protected $perPage = 5;
    protected static $hasDefault = ['user_id', 'season_id', 'date'];
}
