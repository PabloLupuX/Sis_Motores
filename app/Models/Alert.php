<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';

    protected $fillable = [
        'idMovimientos',  
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'idMovimientos' => 'array',
        'fecha' => 'date',
    ];

    /**
     * Relación con los movimientos.
     */
    public function movimientos()
    {
        if (empty($this->idMovimientos)) {
            return collect();
        }
        return Movement::whereIn('id', $this->idMovimientos)->get();
    }
}
