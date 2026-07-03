<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@pqrsf.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $this->call([
            FormFieldSeeder::class,
            SedeSeeder::class,
            PqrsfSubmissionSeeder::class,
        ]);
    }
}
