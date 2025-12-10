<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Ejemplos manuales
        Customer::create([
            'codigo'   => '12345678',
            'nombres'  => 'Juan Pérez',
            'alias'    => 'JP',
            'telefono' => '987654321',
            'state'    => true,
        ]);

        Customer::create([
            'codigo'   => '87654321011',
            'nombres'  => 'María López',
            'alias'    => 'ML',
            'telefono' => '912345678',
            'state'    => false,
        ]);

        // Automáticos con Faker
        for ($i = 0; $i < 20; $i++) {

            // Generar código entre 8 y 11 dígitos
            $length = rand(8, 11);
            $codigo = '';
            for ($x = 0; $x < $length; $x++) {
                $codigo .= rand(0, 9);
            }

            Customer::create([
                'codigo'   => $codigo,
                'nombres'  => $faker->name(),
                'alias'    => $faker->userName(),
                'telefono' => $faker->numerify('#########'), // 9 dígitos
                'state'    => (bool) rand(0, 1),
            ]);
        }
    }
}
