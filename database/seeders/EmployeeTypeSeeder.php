<?php

namespace Database\Seeders;

use App\Models\EmployeeType;
use Illuminate\Database\Seeder;

class EmployeeTypeSeeder extends Seeder{
    public function run(): void{
        EmployeeType::create([
            'name' => 'Administrador',
            'state' => true,
        ]);
        EmployeeType::create([
            'name' => 'Docente',
            'state' => true,
        ]);
        EmployeeType::create([
            'name' => 'Limpieza',
            'state' => true,
        ]);
        EmployeeType::create([
            'name' => 'Asistente',
            'state' => true,
        ]);
        EmployeeType::create([
            'name' => 'Supervisor',
            'state' => true,
        ]);
        //EmployeeType::factory(600)->create();
    }
}
