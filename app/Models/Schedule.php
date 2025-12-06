<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedule_table'; // nombre explícito porque no sigue la convención plural

    protected $fillable = [
        'fecha',
        'fechaInicio',
        'fechaFin',
        'idEspacio',
        'idEmpleado',
        'state',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'fechaInicio' => 'datetime',
        'fechaFin'    => 'datetime',
        'state' => 'boolean',
    ];

    // Relaciones
    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'idEspacio', 'id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'idEmpleado', 'id');
    }
}
