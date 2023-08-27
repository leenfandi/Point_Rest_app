<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableTime extends Model
{
    use HasFactory;
    protected $fillable = [
        'end_time',
        'start_time',
        'reservation_id',
        'table_day_id',
        
    ];
}
