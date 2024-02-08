<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogsModel extends Model
{
    use HasFactory;
    protected $table = "logs";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = [
        'id',
        'dateTime',
        'userID',
        'action_type',
        'table_affected',
        'old_data',
        'new_data',
    ];
}
