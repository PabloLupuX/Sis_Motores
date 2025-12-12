<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reception extends Model
{
    use HasFactory;

    protected $fillable = [
        'engine_id',
        'customer_owner_id',
        'customer_contact_id',
        'fecha_ingreso',
        'fecha_resuelto',
        'fecha_entrega',
        "problema",
        "tipo_mantenimiento",
        'state',
        'numero_serie'
    ];

    // ─────────────────────────────
    // RELACIONES
    // ─────────────────────────────

    // Motor asociado
    public function engine()
    {
        return $this->belongsTo(Engine::class);
    }

    // Cliente dueño del motor
    public function owner()
    {
        return $this->belongsTo(Customer::class, 'customer_owner_id');
    }

    // Cliente que entrega/retira el motor
    public function contact()
    {
        return $this->belongsTo(Customer::class, 'customer_contact_id');
    }

    // Archivos multimedia asociados
    public function media()
    {
        return $this->hasMany(ReceptionMedia::class);
    }
public function accessories()
{
    return $this->belongsToMany(Accessory::class, 'accessory_reception')
        ->withTimestamps(); // 👈 IMPORTANTE
}


}
