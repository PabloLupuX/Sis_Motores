<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Engine;

class EngineSeeder extends Seeder
{
    public function run(): void
    {
        // Motores de ejemplo manuales
        Engine::create([
            'hp'     => '150HP',
            'tipo'   => 'Diesel',
            'marca'  => 'Cummins',
            'modelo' => 'QSB6.7',
            'year'    => 2020,
            'state'    => true,
        ]);

        Engine::create([
            'hp'     => '200HP',
            'tipo'   => 'Gasolina',
            'marca'  => 'Honda',
            'modelo' => 'GX630',
            'year'    => 2022,
            'state'    => true,
        ]);

        // Motores aleatorios (Faker)
        for ($i = 0; $i < 20; $i++) {
            Engine::create([
                'hp'     => fake()->numberBetween(50, 500) . 'HP',
                'tipo'   => fake()->randomElement(['Diesel', 'Gasolina', '2T', '4T']),
                'marca'  => fake()->randomElement(['Yamaha', 'Honda', 'Caterpillar', 'Volvo Penta', 'Cummins']),
                'modelo' => fake()->bothify('MOD###-??'),
                'year'    => fake()->numberBetween(1990, 2025),
                'state'    => (bool) rand(0, 1),
            ]);
        }
    }
}
