<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

//use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'apellido',
        'codigo',
        'employee_type_id',
        'idHuella',
        'foto',
        'state',
    ];

    protected $casts = [
        'state' => 'boolean',
    ];

    public function empleadoType(): BelongsTo{
        return $this->belongsTo(EmployeeType::class, 'employee_type_id', 'id');
    }
    
    public function Schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'idEmpleado', 'id');
    }

    public function tieneRelaciones(): bool
    {
        //se agrega todas las relaciones que existan
        return $this->Schedules()->exists();
    }
    // app/Models/Employee.php
public function movements()
{
    return $this->belongsToMany(Movement::class, 'EmployeeMovement', 'idEmpleado', 'idMovimiento');
}

}
