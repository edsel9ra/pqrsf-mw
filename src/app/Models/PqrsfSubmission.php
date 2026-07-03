<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PqrsfSubmission extends Model
{
    protected $fillable = [
        'sede_id',
        'field_values',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'field_values' => 'array',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SubmissionLog::class, 'submission_id');
    }
}
