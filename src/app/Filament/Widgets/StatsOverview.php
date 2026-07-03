<?php

namespace App\Filament\Widgets;

use App\Models\PqrsfSubmission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class StatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Resumen operativo';

    protected ?string $description = 'Indicadores principales del periodo seleccionado.';

    protected int|array|null $columns = [
        'default' => 1,
        'md' => 2,
        'xl' => 5,
    ];

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

    protected function getStats(): array
    {
        $base = PqrsfSubmission::query();
        $base = $this->applyDateFilter($base);

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

        $avgAll = $ratings ? round(($ratings->ambientacion + $ratings->atencion + $ratings->comida + $ratings->tiempo) / 4, 1) : 0;

        return [
            Stat::make('Total PQRSF', $total)
                ->description('Todas las solicitudes')
                ->icon('heroicon-o-inbox-stack')
                ->color('gray'),

            Stat::make('Pendientes', $pending)
                ->description('Esperando validación')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Validados', $validated)
                ->description('Validados, sin enviar')
                ->icon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Enviados', $sent)
                ->description('Correo enviado a destinatarios')
                ->icon('heroicon-o-envelope')
                ->color('success'),

            Stat::make('Promedio general', "{$avgAll}/5")
                ->description('Calificación global promedio')
                ->icon('heroicon-o-star')
                ->color($avgAll >= 4 ? 'success' : ($avgAll >= 3 ? 'warning' : 'danger')),
        ];
    }
}
