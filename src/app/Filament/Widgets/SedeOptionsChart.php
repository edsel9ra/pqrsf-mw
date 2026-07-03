<?php

namespace App\Filament\Widgets;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class SedeOptionsChart extends ChartWidget
{
    protected ?string $heading = 'Opciones por sede';

    protected ?string $description = 'Distribución de peticiones, quejas, reclamos, sugerencias y felicitaciones.';

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
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(field_values, '$.opcion_a_calificar')) as opcion"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('sede_id', 'opcion')
            ->get()
            ->groupBy('sede_id');

        $options = ['Queja', 'Reclamo', 'Petición', 'Sugerencia', 'Felicitación'];
        $optionColors = [
            'Queja' => 'rgba(239, 68, 68, 0.8)',
            'Reclamo' => 'rgba(245, 158, 11, 0.8)',
            'Petición' => 'rgba(59, 130, 246, 0.8)',
            'Sugerencia' => 'rgba(107, 114, 128, 0.8)',
            'Felicitación' => 'rgba(34, 197, 94, 0.8)',
        ];
        $optionBorderColors = [
            'Queja' => 'rgb(239, 68, 68)',
            'Reclamo' => 'rgb(245, 158, 11)',
            'Petición' => 'rgb(59, 130, 246)',
            'Sugerencia' => 'rgb(107, 114, 128)',
            'Felicitación' => 'rgb(34, 197, 94)',
        ];

        $datasets = [];
        foreach ($options as $option) {
            $data = [];
            foreach ($sedes as $id => $nombre) {
                $data[] = (int) ($rows->get($id)?->firstWhere('opcion', $option)?->total ?? 0);
            }
            $datasets[] = [
                'label' => $option,
                'data' => $data,
                'backgroundColor' => $optionColors[$option],
                'borderColor' => $optionBorderColors[$option],
                'borderWidth' => 1,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $sedes->values()->toArray(),
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
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'ticks' => [
                        'precision' => 0,
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
