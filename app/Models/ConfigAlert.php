<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigAlert extends Model
{
    use HasFactory;

    protected $table = 'alert_configuration'; // nombre de la tabla

    protected $fillable = [
        'time',
        'amount',
    ];

    protected $casts = [
        'time' => 'decimal:2', // mantiene precisión de 2 decimales
        'amount' => 'integer',
    ];
}
