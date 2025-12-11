<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accessory;
use Illuminate\Support\Carbon;

class AccessorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $accessories = [
            ['name' => 'TAPA O CUBIERTA DE MOTOR',           'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TANQUE DE COMBUSTIBLE',              'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MANGUERA DE COMBUSTIBLE COMPLETA',   'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LLAVE TIPO CANGREJO',                'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'SILENCIADOR DE ADMISION',            'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'SELENIO PARA CARGA DE BATERIA',      'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PLATO DE ARRANQUE COMPLETO',         'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'HÉLICE EN BUEN ESTADO',              'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CABLES PARA CARGA DE BATERIA',       'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LLAVE DE CONTACTO ELÉCTRICO',        'state' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        Accessory::insert($accessories);
    }
}
