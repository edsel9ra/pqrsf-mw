<?php

namespace App\Filament\Widgets;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class RatingPercentagesBySedeTable extends Widget
{
    protected string $view = 'filament.widgets.rating-percentages-by-sede-table';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 97;

    public ?array $pageFilters = [];

    public array $rows = [];

    public array $grandTotal = [];

    #[On('filters-updated')]
    public function onFiltersUpdated($date_from = null, $date_to = null): void
    {
        $this->pageFilters = [
            'date_from' => $date_from,
            'date_to' => $date_to,
        ];
        $this->loadData();
    }

    public function mount(): void
    {
        $this->loadData();
    }

    protected function applyDateFilter($query)
    {
        $from = $this->pageFilters['date_from'] ?? null;
        $to = $this->pageFilters['date_to'] ?? null;
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    protected function loadData(): void
    {
        $sedes = Sede::orderBy('nombre')->pluck('nombre', 'id');

        $base = PqrsfSubmission::query();
        $base = $this->applyDateFilter($base);

        $ratings = (clone $base)
            ->select(
                'sede_id',
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_atencion")), 4) as atencion'),
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_comida")), 4) as comida'),
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_tiempo")), 4) as tiempo'),
                DB::raw('ROUND(AVG(JSON_EXTRACT(field_values, "$.calificacion_ambientacion")), 4) as ambientacion'),
            )
            ->groupBy('sede_id')
            ->get()
            ->keyBy('sede_id');

        $data = [];
        $grandTotals = ['atencion' => 0, 'comida' => 0, 'tiempo' => 0, 'ambientacion' => 0, 'promedio' => 0];
        $count = 0;

        foreach ($sedes as $id => $nombre) {
            $r = $ratings[$id] ?? null;
            if (!$r) {
                continue;
            }
            $ate = (float) ($r->atencion ?? 0);
            $com = (float) ($r->comida ?? 0);
            $tie = (float) ($r->tiempo ?? 0);
            $amb = (float) ($r->ambientacion ?? 0);
            $prom = ($ate + $com + $tie + $amb) / 4;

            $data[] = [
                'sede' => $nombre,
                'atencion' => round(($ate / 5) * 100, 2),
                'comida' => round(($com / 5) * 100, 2),
                'tiempo' => round(($tie / 5) * 100, 2),
                'ambientacion' => round(($amb / 5) * 100, 2),
                'promedio' => round(($prom / 5) * 100, 2),
            ];
            foreach (['atencion', 'comida', 'tiempo', 'ambientacion', 'promedio'] as $key) {
                $grandTotals[$key] += end($data)[$key];
            }
            $count++;
        }

        if ($count > 0) {
            $this->grandTotal = [
                'atencion' => round($grandTotals['atencion'] / $count, 2),
                'comida' => round($grandTotals['comida'] / $count, 2),
                'tiempo' => round($grandTotals['tiempo'] / $count, 2),
                'ambientacion' => round($grandTotals['ambientacion'] / $count, 2),
                'promedio' => round($grandTotals['promedio'] / $count, 2),
            ];
        }

        $this->rows = $data;
    }
}
