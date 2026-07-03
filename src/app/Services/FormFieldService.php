<?php

namespace App\Services;

use App\Models\FormField;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FormFieldService
{
    public static function defaultFields(): array
    {
        return [
            'fecha' => ['label' => 'Fecha', 'type' => 'text', 'options' => null, 'validation_rules' => null, 'orden' => 1, 'requerido' => false, 'activo' => true],
            'sede_id' => ['label' => 'Sede', 'type' => 'select', 'options' => null, 'validation_rules' => null, 'orden' => 2, 'requerido' => true, 'activo' => true],
            'nombre_completo' => ['label' => 'Nombre Completo', 'type' => 'text', 'options' => null, 'validation_rules' => ['max:255'], 'orden' => 3, 'requerido' => true, 'activo' => true],
            'numero_movil' => ['label' => 'Número Móvil', 'type' => 'tel', 'options' => null, 'validation_rules' => ['max:20'], 'orden' => 4, 'requerido' => true, 'activo' => true],
            'correo_electronico' => ['label' => 'Correo Electrónico', 'type' => 'email', 'options' => null, 'validation_rules' => ['max:255'], 'orden' => 5, 'requerido' => true, 'activo' => true],
            'opcion_a_calificar' => ['label' => 'Opción a Calificar', 'type' => 'select', 'options' => ['Petición', 'Queja', 'Reclamo', 'Sugerencia', 'Felicitación'], 'validation_rules' => null, 'orden' => 6, 'requerido' => true, 'activo' => true],
            'nombre_mesero' => ['label' => 'Nombre del Mesero', 'type' => 'text', 'options' => null, 'validation_rules' => ['max:255'], 'orden' => 7, 'requerido' => false, 'activo' => true],
            'calificacion_ambientacion' => ['label' => 'Ambientación (Experiencia)', 'type' => 'rating', 'options' => null, 'validation_rules' => ['min:1', 'max:5'], 'orden' => 8, 'requerido' => true, 'activo' => true],
            'calificacion_atencion' => ['label' => 'Atención a la Mesa', 'type' => 'rating', 'options' => null, 'validation_rules' => ['min:1', 'max:5'], 'orden' => 9, 'requerido' => true, 'activo' => true],
            'calificacion_comida' => ['label' => 'Calidad de la Comida', 'type' => 'rating', 'options' => null, 'validation_rules' => ['min:1', 'max:5'], 'orden' => 10, 'requerido' => true, 'activo' => true],
            'calificacion_tiempo' => ['label' => 'Tiempo de Entrega', 'type' => 'rating', 'options' => null, 'validation_rules' => ['min:1', 'max:5'], 'orden' => 11, 'requerido' => true, 'activo' => true],
            'recomendaria' => ['label' => '¿Recomendaría este restaurante a un familiar o amigo?', 'type' => 'boolean', 'options' => null, 'validation_rules' => null, 'orden' => 12, 'requerido' => true, 'activo' => true],
            'observaciones' => ['label' => 'Observaciones', 'type' => 'textarea', 'options' => null, 'validation_rules' => ['max:2000'], 'orden' => 13, 'requerido' => false, 'activo' => true],
            'medio_conocimiento' => ['label' => '¿Por qué medio conoció el restaurante?', 'type' => 'checkbox_list', 'options' => ['Redes Sociales', 'Google', 'Recomendación', 'Publicidad', 'Otro'], 'validation_rules' => null, 'orden' => 14, 'requerido' => false, 'activo' => true],
            'autorizacion_datos' => ['label' => 'Autorización de datos personales', 'type' => 'boolean', 'options' => null, 'validation_rules' => null, 'orden' => 15, 'requerido' => true, 'activo' => true],
        ];
    }

    public static function activeFields(): Collection
    {
        $fields = FormField::activos()->ordenados()->get();

        if ($fields->isEmpty()) {
            return collect(self::defaultFields())->map(fn (array $field, string $key) => new FormField([
                ...$field,
                'key' => $key,
            ]))->values();
        }

        return $fields
            ->map(function (FormField $field): FormField {
                $field->key = $field->key ?: self::keyFromLabel($field->label);

                $defaults = self::defaultFields()[$field->key] ?? [];
                $field->options = $field->options ?: ($defaults['options'] ?? null);
                $field->validation_rules = $field->validation_rules ?: ($defaults['validation_rules'] ?? null);

                return $field;
            })
            ->filter(fn (FormField $field): bool => filled($field->key))
            ->values();
    }

    public static function keyFromLabel(string $label): string
    {
        return Str::slug($label, '_');
    }
}
