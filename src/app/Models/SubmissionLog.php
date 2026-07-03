<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionLog extends Model
{
    protected $fillable = [
        'submission_id',
        'user_id',
        'action',
        'notas',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(PqrsfSubmission::class, 'submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
