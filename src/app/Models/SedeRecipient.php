<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SedeRecipient extends Model
{
    protected $fillable = [
        'sede_id',
        'email',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }
}
