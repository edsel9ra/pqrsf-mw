<?php

namespace App\Filament\Widgets;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class RatingsChart extends ChartWidget
{
    protected ?string $heading = 'Promedio de calificaciones por sede';

    protected ?string $description = 'Compara ambientación, atención, comida y tiempo de entrega por sede.';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = '60s';

    public ?array $pageFilters = [];

    #[On('filters-updated')]
    public function onFiltersUpdated($date_from = null, $date_to = null): void
    {
        $this->pageFilters = [
            'date_from' => $date_from,
            'date_to' => $date_to,
        ];
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

    protected function getData(): array
    {
        $sedes = Sede::pluck('nombre', 'id');

        $base = PqrsfSubmission::query();
        $base = $this->applyDateFilter($base);

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

        $palette = [
            ['bg' => 'rgba(251, 191, 36, 0.8)', 'border' => 'rgb(251, 191, 36)'],
            ['bg' => 'rgba(59, 130, 246, 0.8)', 'border' => 'rgb(59, 130, 246)'],
            ['bg' => 'rgba(34, 197, 94, 0.8)', 'border' => 'rgb(34, 197, 94)'],
            ['bg' => 'rgba(239, 68, 68, 0.8)', 'border' => 'rgb(239, 68, 68)'],
            ['bg' => 'rgba(168, 85, 247, 0.8)', 'border' => 'rgb(168, 85, 247)'],
        ];

        $categories = ['ambientacion', 'atencion', 'comida', 'tiempo'];
        $labels = ['Ambientación', 'Atención a la mesa', 'Calidad de la comida', 'Tiempo de entrega'];

        $datasets = [];
        $idx = 0;
        foreach ($sedes as $id => $nombre) {
            $sedeRatings = $rows[$id] ?? null;
            $datasets[] = [
                'label' => $nombre,
                'data' => array_map(fn ($cat) => (float) ($sedeRatings->$cat ?? 0), $categories),
                'backgroundColor' => $palette[$idx % count($palette)]['bg'],
                'borderColor' => $palette[$idx % count($palette)]['border'],
                'borderWidth' => 1,
            ];
            $idx++;
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 5,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
