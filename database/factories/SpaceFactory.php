<?php

namespace Database\Factories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceFactory extends Factory
{
    protected $model = Space::class; // Indica el modelo asociado

    public function definition(): array
    {
        return [
            // 'name' es único
            'name' => $this->faker->unique()->company(),
            // 'description' puede ser vacía o generada aleatoriamente
            'description' => $this->faker->optional()->sentence(),
            // 'state' es booleano
            'state' => $this->faker->boolean(),
        ];
    }
}
