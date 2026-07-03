<?php

namespace Database\Seeders;

use App\Models\Sede;
use App\Models\SedeRecipient;
use Illuminate\Database\Seeder;

class SedeSeeder extends Seeder
{
    public function run(): void
    {
        $sedes = [
            ['nombre' => 'Sede Principal', 'direccion' => 'Calle 123 #45-67, Centro', 'telefono' => '1234567', 'email' => 'principal@restaurante.com'],
            ['nombre' => 'Sede Norte', 'direccion' => 'Av. Norte #23-45', 'telefono' => '2345678', 'email' => 'norte@restaurante.com'],
            ['nombre' => 'Sede Sur', 'direccion' => 'Carrera 5 #12-34, Sur', 'telefono' => '3456789', 'email' => 'sur@restaurante.com'],
        ];

        foreach ($sedes as $data) {
            $sede = Sede::create($data);

            SedeRecipient::create([
                'sede_id' => $sede->id,
                'email' => "admin-{$sede->id}@restaurante.com",
                'nombre' => "Admin {$sede->nombre}",
            ]);

            SedeRecipient::create([
                'sede_id' => $sede->id,
                'email' => "gerente-{$sede->id}@restaurante.com",
                'nombre' => "Gerente {$sede->nombre}",
            ]);
        }
    }
}
