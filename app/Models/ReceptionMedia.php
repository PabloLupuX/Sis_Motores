<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceptionMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'reception_id',
        'type',
        'url'
    ];

    // Relación: este archivo pertenece a una recepción
    public function reception()
    {
        return $this->belongsTo(Reception::class);
    }
}
