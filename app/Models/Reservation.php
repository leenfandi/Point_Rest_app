<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'end_time',
        'start_time',
        'day',
        'done',
        'status',
        'tabel_id',
    ];
}
