<?php

namespace App\Services\Reports;

use App\Models\PqrsfSubmission;
use App\Services\ReportFilters;
use App\Services\SubmissionReportQuery;
use Illuminate\Support\Collection;

class SubmissionReportService
{
    public function __construct(private readonly SubmissionReportQuery $query) {}

    public function getData(array $filters): array
    {
        $rows = $this->getRows($filters);

        return [
            'filters' => $filters,
            'filterLabels' => ReportFilters::labels($filters),
            'rows' => $rows,
            'total' => $rows->count(),
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ];
    }

    public function getRows(array $filters): Collection
    {
        return $this->query->make($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PqrsfSubmission $submission): array => $this->mapSubmission($submission))
            ->values();
    }

    private function mapSubmission(PqrsfSubmission $submission): array
    {
        $values = $submission->field_values ?? [];

        return [
            'fecha' => $submission->created_at?->format('d/m/Y H:i') ?? '—',
            'sede' => $submission->sede?->nombre ?? '—',
            'nombre_completo' => $this->displayValue($values['nombre_completo'] ?? null),
            'nombre_mesero' => $this->displayValue($values['nombre_mesero'] ?? null),
        ];
    }

    private function displayValue(mixed $value): string
    {
        if (is_array($value)) {
            $value = collect($value)
                ->filter(fn ($item): bool => is_scalar($item) && filled($item))
                ->map(fn ($item): string => trim((string) $item))
                ->implode(', ');
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : '—';
    }
}
