<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movement extends Model
{
    protected $table = 'movimientos';
    use HasFactory;

    protected $fillable = [
        'idEspacio',
        'idTipo',
        'reconocido',
        'access',
        'error',
        'fechaEnvioESP32',
        'fechaRecepcion',
        'fechaReconocimiento',
    ];

    protected $casts = [
        'reconocido' => 'boolean',
        'access' => 'boolean',
    ];

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'idEspacio', 'id');
    }
    public function tieneRelaciones(): bool
    {
        return Alert::whereJsonContains('idMovimientos', $this->id)->exists();
    }
    // app/Models/Movement.php
public function employees()
{
    return $this->belongsToMany(Employee::class, 'EmployeeMovement', 'idMovimiento', 'idEmpleado');
}

}
