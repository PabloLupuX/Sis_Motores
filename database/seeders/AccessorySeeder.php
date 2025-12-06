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
            ['name' => 'Arrancador',            'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Alternador',            'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Radiador',              'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Inyector',              'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Filtro de aire',        'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Filtro de combustible', 'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Turbocargador',         'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bomba de aceite',       'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bomba de agua',         'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sensor de temperatura', 'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sensor de presión',     'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Polea',                 'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Faja / Correa',         'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Válvula EGR',           'state' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Batería',               'state' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        Accessory::insert($accessories);
    }
}
