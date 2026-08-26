<?php

namespace App\Services\Reports;

use App\Models\PqrsfSubmission;
use App\Services\ReportFilters;
use App\Services\SubmissionReportQuery;
use Illuminate\Support\Collection;

class ObservationsReportService
{
    public function __construct(private readonly SubmissionReportQuery $query) {}

    public function getData(array $filters): array
    {
        $rows = $this->getRows($filters);
        $groups = $this->getGroups($rows);

        return [
            'filters' => $filters,
            'filterLabels' => ReportFilters::labels($filters),
            'rows' => $rows,
            'groups' => $groups,
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
            ->map(function (PqrsfSubmission $submission): ?array {
                $values = $submission->field_values ?? [];
                $observations = trim((string) ($values['observaciones'] ?? ''));

                if ($observations === '') {
                    return null;
                }

                return [
                    'sede_id' => $submission->sede_id,
                    'sede' => $submission->sede?->nombre ?? '—',
                    'nombre_completo' => $this->displayValue($values['nombre_completo'] ?? null),
                    'opcion_calificada' => $this->displayValue($values['opcion_a_calificar'] ?? null),
                    'observaciones' => $observations,
                    'nombre_mesero' => $this->displayValue($values['nombre_mesero'] ?? null),
                ];
            })
            ->filter()
            ->values();
    }

    public function getGroups(Collection $rows): Collection
    {
        return $rows
            ->groupBy('sede_id')
            ->map(function (Collection $group): array {
                $first = $group->first();

                return [
                    'sede_id' => $first['sede_id'],
                    'sede' => $first['sede'],
                    'rows' => $group->values(),
                    'total' => $group->count(),
                ];
            })
            ->sortBy(fn (array $group): string => mb_strtolower($group['sede']))
            ->values();
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
