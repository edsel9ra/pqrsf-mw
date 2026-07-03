<?php

namespace Database\Factories;

use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'label' => fake()->words(3, true),
            'key' => Str::slug(fake()->unique()->words(3, true), '_'),
            'type' => 'text',
            'options' => null,
            'validation_rules' => null,
            'orden' => fake()->numberBetween(1, 20),
            'requerido' => false,
            'activo' => true,
        ];
    }
}
