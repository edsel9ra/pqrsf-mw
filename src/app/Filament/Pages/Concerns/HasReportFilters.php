<?php

namespace App\Filament\Pages\Concerns;

use App\Models\Sede;
use App\Services\ReportFilters;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

trait HasReportFilters
{
    public ?array $filterData = [];

    public array $reportData = [];

    public array $appliedFilters = [];

    public bool $showReport = false;

    protected function reportForm(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('filterData.sede_id')
                    ->label('Sede')
                    ->placeholder('Todas las sedes')
                    ->options(fn () => Sede::orderBy('nombre')->pluck('nombre', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false),
                DatePicker::make('filterData.date_from')
                    ->label('Desde')
                    ->native(false),
                DatePicker::make('filterData.date_to')
                    ->label('Hasta')
                    ->native(false),
            ])
            ->columns([
                'default' => 1,
                'md' => 2,
                'xl' => 3,
            ]);
    }

    public function getDownloadUrl(string $format): string
    {
        $params = array_filter(
            $this->appliedFilters,
            fn ($value) => $value !== null && $value !== '',
        );

        return route($this->reportRouteName().'.'.$format, $params);
    }

    protected function validatedReportFilters(): ?array
    {
        try {
            return ReportFilters::validate($this->reportFilterState());
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'Revise los filtros seleccionados.';

            Notification::make()
                ->title('Filtros inválidos')
                ->body($message)
                ->danger()
                ->send();

            return null;
        }
    }

    protected function reportFilterState(): array
    {
        $state = $this->form->getState();
        $filters = $state['filterData'] ?? [];

        return [
            'sede_id' => $filters['sede_id'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ];
    }

    abstract protected function reportRouteName(): string;
}
