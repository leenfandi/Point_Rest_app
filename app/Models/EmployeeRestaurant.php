<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRestaurant extends Model
{
    use HasFactory;
    protected $fillable = [
        'rustaurant_id',
        'user_id',
    ];
}
