<?php

namespace App\Filament\Widgets;

use App\Models\PqrsfSubmission;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class SubmissionsChart extends ChartWidget
{
    protected ?string $heading = 'PQRSF por día';

    protected ?string $description = 'Tendencia diaria del periodo filtrado o de los últimos 30 días.';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

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
        $base = PqrsfSubmission::query();
        $base = $this->applyDateFilter($base);

        $from = $this->pageFilters['date_from'] ?? null;
        $to = $this->pageFilters['date_to'] ?? null;

        if (! $from && ! $to) {
            $base->where('created_at', '>=', now()->subDays(30));
        }

        $data = (clone $base)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        if ($from && $to) {
            $periodStart = Carbon::parse($from);
            $periodEnd = Carbon::parse($to);
        } elseif ($from) {
            $periodStart = Carbon::parse($from);
            $periodEnd = $periodStart->copy()->addDays(30);
        } elseif ($to) {
            $periodEnd = Carbon::parse($to);
            $periodStart = $periodEnd->copy()->subDays(30);
        } else {
            $periodStart = now()->subDays(30);
            $periodEnd = now();
        }

        $dates = collect();
        $current = $periodStart->copy();
        while ($current->lte($periodEnd)) {
            $dates->push($current->format('Y-m-d'));
            $current->addDay();
        }

        $values = $dates->map(fn ($date) => $data[$date] ?? 0);

        return [
            'datasets' => [
                [
                    'label' => 'PQRSF registradas',
                    'data' => $values->toArray(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                    'borderColor' => 'rgb(251, 191, 36)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'pointRadius' => 3,
                    'pointBackgroundColor' => 'rgb(251, 191, 36)',
                ],
            ],
            'labels' => $dates->map(fn ($d) => Carbon::parse($d)->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'tensions' => 0.3,
        ];
    }
}
