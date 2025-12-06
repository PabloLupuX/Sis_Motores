<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engine extends Model
{
    protected $fillable = [
        'hp',
        'tipo',
        'marca',
        'modelo',
        'year',
        'state',
    ];
}
