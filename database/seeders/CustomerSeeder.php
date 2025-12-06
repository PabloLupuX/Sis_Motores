<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
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
            Customer::create([
                'codigo'   => str_pad(rand(10000000, 99999999999), rand(8, 11), '0', STR_PAD_LEFT),
                'nombres'  => fake()->name(),
                'alias'    => fake()->userName(),
                'telefono' => str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT),
                'state'    => (bool) rand(0, 1),
            ]);
        }
    }
}
