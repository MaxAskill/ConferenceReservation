<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservedSchedules extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = "reserved_schedules";
    protected $primaryKey = "id";
    protected $fillable = [
        'id',
        'event_id',
        'date',
        'employee_name',
        'time_start',
        'time_end',
        'email',
        'department',
        'purpose',
        'equipment',
        'ct_arrangement',
        'status',
        'çonference_room'
    ];
}
