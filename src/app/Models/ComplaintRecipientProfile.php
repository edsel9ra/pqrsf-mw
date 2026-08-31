<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ComplaintRecipientProfile extends Model
{
    public const TEMPLATE_FULL = 'full';

    public const TEMPLATE_WITHOUT_CONTACT_DATA = 'without_contact_data';

    protected $fillable = [
        'email',
        'template_key',
        'excluded_field_keys',
    ];

    protected function casts(): array
    {
        return [
            'excluded_field_keys' => 'array',
        ];
    }

    public static function templateOptions(): array
    {
        return [
            self::TEMPLATE_FULL => 'Plantilla completa',
            self::TEMPLATE_WITHOUT_CONTACT_DATA => 'Sin datos de contacto',
        ];
    }

    public static function fieldOptions(): array
    {
        return [
            'nombre_completo' => 'Nombre completo',
            'numero_movil' => 'Número móvil',
            'correo_electronico' => 'Correo electrónico',
            'nombre_mesero' => 'Nombre del mesero',
            'recomendaria' => 'Recomendación',
            'medio_conocimiento' => 'Medio de conocimiento',
            'calificacion_ambientacion' => 'Calificación de ambientación',
            'calificacion_atencion' => 'Calificación de atención',
            'calificacion_comida' => 'Calificación de comida',
            'calificacion_tiempo' => 'Calificación de tiempo',
            'observaciones' => 'Observaciones',
        ];
    }

    public static function withoutContactDataFieldKeys(): array
    {
        return [
            'numero_movil',
            'correo_electronico',
        ];
    }

    public static function normalizeEmail(?string $email): ?string
    {
        return filled($email) ? Str::lower(trim($email)) : null;
    }

    public function effectiveExcludedFieldKeys(): array
    {
        $keys = $this->excluded_field_keys ?? [];

        if ($this->template_key === self::TEMPLATE_WITHOUT_CONTACT_DATA) {
            $keys = array_merge(self::withoutContactDataFieldKeys(), $keys);
        }

        return collect($keys)
            ->filter(fn (mixed $key): bool => is_string($key) && array_key_exists($key, self::fieldOptions()))
            ->unique()
            ->values()
            ->all();
    }

    protected static function booted(): void
    {
        static::saving(function (self $profile): void {
            $profile->email = self::normalizeEmail($profile->email);
            $profile->excluded_field_keys = collect($profile->excluded_field_keys ?? [])
                ->filter(fn (mixed $key): bool => is_string($key) && array_key_exists($key, self::fieldOptions()))
                ->unique()
                ->values()
                ->all();
        });
    }
}
