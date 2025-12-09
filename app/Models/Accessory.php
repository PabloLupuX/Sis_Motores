<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{
    use HasFactory;

    protected $table = 'accessories';

    protected $fillable = [
        'name',
        'state',
    ];
    protected $casts = [
        'state' => 'boolean',
    ];
public function receptions()
{
    return $this->belongsToMany(Reception::class, 'accessory_reception')
        ->withTimestamps(); // 👈 Opcional pero buena práctica
}


}
