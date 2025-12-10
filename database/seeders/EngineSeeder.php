<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Engine;
use Faker\Factory as Faker;

class EngineSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Motores manuales
        Engine::create([
            'hp'     => '150HP',
            'tipo'   => 'Diesel',
            'marca'  => 'Cummins',
            'modelo' => 'QSB6.7',
            'year'   => 2020,
            'state'  => true,
        ]);

        Engine::create([
            'hp'     => '200HP',
            'tipo'   => 'Gasolina',
            'marca'  => 'Honda',
            'modelo' => 'GX630',
            'year'   => 2022,
            'state'  => true,
        ]);

        // Motores aleatorios seguros
        for ($i = 0; $i < 20; $i++) {

            Engine::create([
                'hp'     => $faker->numberBetween(50, 500) . 'HP',
                'tipo'   => $faker->randomElement(['Diesel', 'Gasolina', '2T', '4T']),
                'marca'  => $faker->randomElement(['Yamaha', 'Honda', 'Caterpillar', 'Volvo Penta', 'Cummins']),
                'modelo' => strtoupper($faker->bothify('MOD###-??')),
                'year'   => $faker->numberBetween(1990, (int) date('Y')),
                'state'  => $faker->boolean(),
            ]);
        }
    }
}
