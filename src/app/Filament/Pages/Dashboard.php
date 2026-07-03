<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestSubmissions;
use App\Filament\Widgets\PqrsfBySedeTable;
use App\Filament\Widgets\RatingPercentagesBySedeTable;
use App\Filament\Widgets\RatingsChart;
use App\Filament\Widgets\SedeOptionsChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\SubmissionsChart;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            RatingsChart::class,
            SedeOptionsChart::class,
            SubmissionsChart::class,
            PqrsfBySedeTable::class,
            RatingPercentagesBySedeTable::class,
            LatestSubmissions::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 12,
        ];
    }

    public function mount(): void
    {
        $this->filters = null;

        if ($this->persistsFiltersInSession()) {
            session()->forget($this->getFiltersSessionKey());
        }

        $this->dispatch('filters-updated', date_from: null, date_to: null);
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('date_from')
                    ->label('Desde')
                    ->native(false),
                DatePicker::make('date_to')
                    ->label('Hasta')
                    ->native(false),
            ])
            ->columns([
                'default' => 1,
                'md' => 2,
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_filters')
                ->label('Limpiar filtros')
                ->color('gray')
                ->action('clearFilters'),
        ];
    }

    public function clearFilters(): void
    {
        $this->filters = null;
        $this->dispatch('filters-updated', date_from: null, date_to: null);
    }

    public function updatedFilters(): void
    {
        if ($this->persistsFiltersInSession()) {
            $hasActiveFilters = is_array($this->filters) && (
                ! empty($this->filters['date_from']) || ! empty($this->filters['date_to'])
            );

            if ($hasActiveFilters) {
                session()->put($this->getFiltersSessionKey(), $this->filters);
            } else {
                session()->forget($this->getFiltersSessionKey());
            }
        }

        $this->dispatch('filters-updated',
            date_from: is_array($this->filters) ? ($this->filters['date_from'] ?? null) : null,
            date_to: is_array($this->filters) ? ($this->filters['date_to'] ?? null) : null,
        );
    }
}
