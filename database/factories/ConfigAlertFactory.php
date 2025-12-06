<?php

namespace Database\Factories;

use App\Models\ConfigAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigAlertFactory extends Factory
{
    protected $model = ConfigAlert::class; // Modelo asociado

    public function definition(): array
    {
        return [
            // 'time' en decimal con dos decimales (ej. 5.50, 10.75)
            'time' => $this->faker->randomFloat(2, 0, 1000), 
            // 'amount' entero desde 0 hasta un valor grande
            'amount' => $this->faker->numberBetween(0, 1000000),
        ];
    }
}
