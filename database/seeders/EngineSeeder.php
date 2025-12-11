<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Engine;

class EngineSeeder extends Seeder
{
    public function run(): void
    {
        // Motores manuales (datos fijos)
        Engine::create([
            'hp'          => '150HP',
            'tipo'        => 'Diesel',
            'marca'       => 'Cummins',
            'modelo'      => 'QSB6.7',
            'combustible' => 'DIESEL',
            'state'       => true,
        ]);

        Engine::create([
            'hp'          => '200HP',
            'tipo'        => 'Gasolina',
            'marca'       => 'Honda',
            'modelo'      => 'GX630',
            'combustible' => 'GASOLINA',
            'state'       => true,
        ]);
    }
}
