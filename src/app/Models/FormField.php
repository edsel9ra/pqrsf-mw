<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'key',
        'type',
        'options',
        'validation_rules',
        'orden',
        'requerido',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'validation_rules' => 'array',
            'requerido' => 'boolean',
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true);
    }

    public function scopeOrdenados(Builder $query): void
    {
        $query->orderBy('orden');
    }
}
