<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableDay extends Model
{
    use HasFactory;
    protected $fillable = [
        'tabel_id',
        'day',
        
    ];
}
