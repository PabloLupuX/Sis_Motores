<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Registros manuales
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
    }
}
