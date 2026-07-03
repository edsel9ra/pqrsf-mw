<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $aliases = [
            'Calificación Ambientación' => 'calificacion_ambientacion',
            'Calificación Atención' => 'calificacion_atencion',
            'Calificación Comida' => 'calificacion_comida',
            'Calificación Tiempo de Entrega' => 'calificacion_tiempo',
            'Medio de Conocimiento' => 'medio_conocimiento',
            'Autorización de Datos' => 'autorizacion_datos',
        ];

        foreach ($aliases as $label => $key) {
            $field = DB::table('form_fields')->where('label', $label)->first();

            if (! $field) {
                continue;
            }

            $hasConflict = DB::table('form_fields')
                ->where('key', $key)
                ->where('id', '!=', $field->id)
                ->exists();

            if (! $hasConflict) {
                DB::table('form_fields')->where('id', $field->id)->update(['key' => $key]);
            }
        }
    }

    public function down(): void
    {
        // No se revierten claves normalizadas para no romper field_values históricos.
    }
};
