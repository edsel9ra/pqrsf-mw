<?php

namespace Database\Seeders;

use App\Models\FormField;
use App\Services\FormFieldService;
use Illuminate\Database\Seeder;

class FormFieldSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FormFieldService::defaultFields() as $key => $field) {
            FormField::create([
                ...$field,
                'key' => $key,
            ]);
        }
    }
}
