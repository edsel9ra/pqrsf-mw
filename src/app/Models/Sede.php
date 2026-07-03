<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function pqrsfSubmissions(): HasMany
    {
        return $this->hasMany(PqrsfSubmission::class);
    }

    public function sedeRecipients(): HasMany
    {
        return $this->hasMany(SedeRecipient::class);
    }
}
