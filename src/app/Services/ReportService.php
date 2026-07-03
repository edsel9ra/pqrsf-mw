<?php

namespace App\Services;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected ?int $sedeId;

    protected ?string $dateFrom;

    protected ?string $dateTo;

    protected ?string $optionType;

    protected ?string $ratingCategory;

    public function __construct(
        ?int $sedeId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $optionType = null,
        ?string $ratingCategory = null,
    ) {
        $this->sedeId = $sedeId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->optionType = $optionType;
        $this->ratingCategory = $ratingCategory;
    }

    public static function make(
        ?int $sedeId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $optionType = null,
        ?string $ratingCategory = null,
    ): self {
        return new self($sedeId, $dateFrom, $dateTo, $optionType, $ratingCategory);
    }

    public function baseQuery(): Builder
    {
        $query = PqrsfSubmission::query();

        if ($this->sedeId) {
            $query->where('sede_id', $this->sedeId);
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        if ($this->optionType) {
            $query->whereRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(field_values, ?)) = ?',
                ['$.opcion_a_calificar', $this->optionType]
            );
        }

        return $query;
    }

    public function getFilterParams(): array
    {
        return [
            'sede_id' => $this->sedeId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'option_type' => $this->optionType,
            'rating_category' => $this->ratingCategory,
        ];
    }

    public function getStats(): array
    {
        $base = $this->baseQuery();
        $total = (clone $base)->count();
        $pending = (clone $base)->where('status', 'pending')->count();
        $validated = (clone $base)->where('status', 'validated')->count();
        $sent = (clone $base)->where('status', 'sent')->count();

        $ratings = (clone $base)->select(
            DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_ambientacion")), 1) as ambientacion'),
            DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_atencion")), 1) as atencion'),
            DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_comida")), 1) as comida'),
            DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_tiempo")), 1) as tiempo'),
        )->first();

        $avgGeneral = $ratings
            ? round(($ratings->ambientacion + $ratings->atencion + $ratings->comida + $ratings->tiempo) / 4, 1)
            : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'validated' => $validated,
            'sent' => $sent,
            'avg_ambientacion' => $ratings?->ambientacion ?? 0,
            'avg_atencion' => $ratings?->atencion ?? 0,
            'avg_comida' => $ratings?->comida ?? 0,
            'avg_tiempo' => $ratings?->tiempo ?? 0,
            'avg_general' => $avgGeneral,
        ];
    }

    public function getRatingsBySede(): Collection
    {
        $base = $this->baseQuery();
        $sedes = Sede::when($this->sedeId, fn ($q) => $q->where('id', $this->sedeId))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $rows = (clone $base)
            ->select(
                'sede_id',
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_ambientacion")), 2) as ambientacion'),
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_atencion")), 2) as atencion'),
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_comida")), 2) as comida'),
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_tiempo")), 2) as tiempo'),
            )
            ->groupBy('sede_id')
            ->get()
            ->keyBy('sede_id');

        $result = collect();
        foreach ($sedes as $id => $nombre) {
            $r = $rows[$id] ?? null;
            $amb = (float) ($r->ambientacion ?? 0);
            $ate = (float) ($r->atencion ?? 0);
            $com = (float) ($r->comida ?? 0);
            $tie = (float) ($r->tiempo ?? 0);
            $prom = round(($amb + $ate + $com + $tie) / 4, 2);
            $result->push((object) [
                'sede_id' => $id,
                'sede_nombre' => $nombre,
                'ambientacion' => $amb,
                'atencion' => $ate,
                'comida' => $com,
                'tiempo' => $tie,
                'promedio' => $prom,
            ]);
        }

        return $result;
    }

    public function getOptionsBreakdown(): Collection
    {
        $base = $this->baseQuery();
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(field_values, '$.opcion_a_calificar')) as opcion")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('opcion')
            ->pluck('total', 'opcion');

        $labels = ['Queja', 'Reclamo', 'Petición', 'Sugerencia', 'Felicitación'];
        $result = collect();
        foreach ($labels as $label) {
            $count = (int) ($rows[$label] ?? 0);
            $result->push((object) [
                'opcion' => $label,
                'total' => $count,
                'porcentaje' => $total > 0 ? round($count / $total * 100, 1) : 0,
            ]);
        }

        return $result;
    }

    public function getStatusDistribution(): Collection
    {
        $base = $this->baseQuery();
        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [
            'pending' => ['label' => 'Pendientes', 'color' => '#f59e0b'],
            'validated' => ['label' => 'Validados', 'color' => '#3b82f6'],
            'sent' => ['label' => 'Enviados', 'color' => '#22c55e'],
        ];

        $result = collect();
        foreach ($labels as $key => $info) {
            $count = (int) ($rows[$key] ?? 0);
            $result->push((object) [
                'status' => $key,
                'label' => $info['label'],
                'total' => $count,
                'color' => $info['color'],
                'porcentaje' => $total > 0 ? round($count / $total * 100, 1) : 0,
            ]);
        }

        return $result;
    }

    public function getDailySubmissions(): Collection
    {
        $base = $this->baseQuery();

        [$dateFrom, $dateTo] = $this->getDailyDateRange($base);

        if (! $dateFrom || ! $dateTo) {
            return collect();
        }

        $data = (clone $base)
            ->select(DB::raw('DATE(created_at) as submission_date'), DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('submission_date')
            ->orderBy('submission_date')
            ->pluck('total', 'submission_date');

        $period = CarbonPeriod::create($dateFrom, $dateTo);
        $result = collect();
        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $result->push((object) [
                'date' => $d,
                'label' => $date->format('d/m'),
                'total' => (int) ($data[$d] ?? 0),
            ]);
        }

        return $result;
    }

    protected function getDailyDateRange(Builder $base): array
    {
        $minCreated = (clone $base)->min('created_at');
        $maxCreated = (clone $base)->max('created_at');

        if (! $maxCreated) {
            return [null, null];
        }

        $minDataDate = Carbon::parse($minCreated)->startOfDay();
        $maxDataDate = Carbon::parse($maxCreated)->startOfDay();

        if ($this->dateFrom) {
            $from = Carbon::parse($this->dateFrom)->startOfDay();
        } else {
            $from = $this->dateTo
                ? $maxDataDate->copy()->subDays(30)
                : $maxDataDate->copy()->subDays(30);

            if ($from->lt($minDataDate)) {
                $from = $minDataDate->copy();
            }
        }

        $to = $this->dateTo
            ? Carbon::parse($this->dateTo)->startOfDay()
            : $maxDataDate->copy();

        if ($to->gt($maxDataDate)) {
            $to = $maxDataDate->copy();
        }

        if ($from->gt($to)) {
            return [null, null];
        }

        return [$from->toDateString(), $to->toDateString()];
    }

    public function getFilteredSubmissions(int $perPage = 50)
    {
        return $this->baseQuery()
            ->with('sede')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getRatingAveragesByCategory(): array
    {
        $base = $this->baseQuery();
        $avgField = fn ($field) => round((float) (clone $base)->avg(DB::raw("JSON_EXTRACT(field_values, '$.{$field}')")) ?: 0, 2);

        $all = [
            'ambientacion' => $avgField('calificacion_ambientacion'),
            'atencion' => $avgField('calificacion_atencion'),
            'comida' => $avgField('calificacion_comida'),
            'tiempo' => $avgField('calificacion_tiempo'),
        ];

        if ($this->ratingCategory && isset($all[$this->ratingCategory])) {
            $val = $all[$this->ratingCategory];

            return [$this->ratingCategory => $val];
        }

        return $all;
    }

    public function getFilterLabels(): array
    {
        $parts = [];
        if ($this->sedeId) {
            $sede = Sede::find($this->sedeId);
            $parts[] = 'Sede: '.($sede?->nombre ?? 'N/A');
        }
        if ($this->dateFrom) {
            $parts[] = 'Desde: '.Carbon::parse($this->dateFrom)->format('d/m/Y');
        }
        if ($this->dateTo) {
            $parts[] = 'Hasta: '.Carbon::parse($this->dateTo)->format('d/m/Y');
        }
        if ($this->optionType) {
            $parts[] = 'Opción: '.$this->optionType;
        }

        return $parts;
    }

    public function getPqrsfBySede(): Collection
    {
        $base = $this->baseQuery();
        $sedes = Sede::when($this->sedeId, fn ($q) => $q->where('id', $this->sedeId))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');

        $options = ['Felicitación', 'Queja', 'Reclamo', 'Sugerencia', 'Petición'];

        $rows = (clone $base)
            ->select(
                'sede_id',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(field_values, '$.opcion_a_calificar')) as opcion"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('sede_id', 'opcion')
            ->get()
            ->groupBy('sede_id');

        $result = collect();
        $grandTotals = array_fill_keys($options, 0);
        $grandTotals['total'] = 0;

        foreach ($sedes as $id => $nombre) {
            $sedeRows = $rows->get($id, collect());
            $row = [];
            $row['sede'] = $nombre;
            $row['total'] = 0;
            foreach ($options as $opt) {
                $count = (int) $sedeRows->firstWhere('opcion', $opt)?->total ?? 0;
                $row[$opt] = $count;
                $row['total'] += $count;
                $grandTotals[$opt] += $count;
                $grandTotals['total'] += $count;
            }
            $result->push((object) $row);
        }

        $footer = new \stdClass;
        $footer->sede = 'TOTAL GENERAL';
        $footer->total = $grandTotals['total'];
        foreach ($options as $opt) {
            $footer->$opt = $grandTotals[$opt];
        }
        $result->push($footer);

        return $result;
    }

    public function getRatingPercentagesBySede(): Collection
    {
        $ratings = $this->getRatingsBySede();

        $result = collect();
        foreach ($ratings as $r) {
            $result->push((object) [
                'sede' => $r->sede_nombre,
                'atencion' => round(($r->atencion / 5) * 100, 2),
                'comida' => round(($r->comida / 5) * 100, 2),
                'tiempo' => round(($r->tiempo / 5) * 100, 2),
                'ambientacion' => round(($r->ambientacion / 5) * 100, 2),
                'promedio' => round(($r->promedio / 5) * 100, 2),
            ]);
        }

        if ($result->isNotEmpty()) {
            $footer = new \stdClass;
            $footer->sede = 'TOTAL GENERAL';
            $footer->atencion = round($result->avg('atencion'), 2);
            $footer->comida = round($result->avg('comida'), 2);
            $footer->tiempo = round($result->avg('tiempo'), 2);
            $footer->ambientacion = round($result->avg('ambientacion'), 2);
            $footer->promedio = round($result->avg('promedio'), 2);
            $result->push($footer);
        }

        return $result;
    }

    public function getAll(): array
    {
        return [
            'filters' => $this->getFilterParams(),
            'filterLabels' => $this->getFilterLabels(),
            'stats' => $this->getStats(),
            'ratingsBySede' => $this->getRatingsBySede(),
            'optionsBreakdown' => $this->getOptionsBreakdown(),
            'statusDistribution' => $this->getStatusDistribution(),
            'dailySubmissions' => $this->getDailySubmissions(),
            'ratingAverages' => $this->getRatingAveragesByCategory(),
            'pqrsfBySede' => $this->getPqrsfBySede(),
            'ratingPercentagesBySede' => $this->getRatingPercentagesBySede(),
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ];
    }
}
