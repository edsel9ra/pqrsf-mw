<?php

namespace App\Services;

use App\Models\PqrsfSubmission;
use Illuminate\Database\Eloquent\Builder;

class SubmissionReportQuery
{
    public function make(array $filters): Builder
    {
        $query = PqrsfSubmission::query()->with('sede:id,nombre');

        if ($filters['sede_id'] !== null) {
            $query->whereIn('sede_id', $filters['sede_id']);
        }

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
