<?php

namespace Database\Factories;

use App\Models\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

class SedeFactory extends Factory
{
    protected $model = Sede::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'direccion' => fake()->address(),
            'telefono' => fake()->phoneNumber(),
            'activo' => true,
        ];
    }
}
