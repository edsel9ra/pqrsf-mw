<?php

namespace Database\Seeders;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PqrsfSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        $sedes = Sede::pluck('id')->toArray();
        $opciones = ['Petición', 'Queja', 'Reclamo', 'Sugerencia', 'Felicitación'];
        $medios = ['Redes Sociales', 'Google', 'Recomendación', 'Publicidad', 'Otro'];
        $meseros = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Pedro', 'Sofía'];

        foreach (range(1, 25) as $i) {
            $values = [
                'fecha' => $faker->date('Y-m-d'),
                'sede_id' => Arr::random($sedes),
                'nombre_completo' => $faker->name(),
                'numero_movil' => $faker->phoneNumber(),
                'correo_electronico' => $faker->email(),
                'opcion_a_calificar' => Arr::random($opciones),
                'nombre_mesero' => Arr::random($meseros),
                'calificacion_ambientacion' => $faker->numberBetween(1, 5),
                'calificacion_atencion' => $faker->numberBetween(1, 5),
                'calificacion_comida' => $faker->numberBetween(1, 5),
                'calificacion_tiempo' => $faker->numberBetween(1, 5),
                'recomendaria' => $faker->boolean(80),
                'observaciones' => $faker->optional(0.7)->sentence(),
                'medio_conocimiento' => $faker->randomElements($medios, $faker->numberBetween(1, 3)),
                'autorizacion_datos' => true,
            ];

            PqrsfSubmission::create([
                'sede_id' => $values['sede_id'],
                'field_values' => $values,
                'status' => Arr::random(['pending', 'validated', 'sent']),
                'ip_address' => $faker->ipv4(),
                'user_agent' => $faker->userAgent(),
                'created_at' => $faker->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }
}
