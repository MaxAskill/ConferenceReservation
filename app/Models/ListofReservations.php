<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeScheduleModel extends Model
{
    use HasFactory;
    protected $table = "timeSchedtb";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = [
        'id',
        'date',
        'employee_name',
        'email',
        'department',
        'purpose',
        'equipment',
        'status',
        'ct_arrangement',
        'çonference_room',
    ];
}
