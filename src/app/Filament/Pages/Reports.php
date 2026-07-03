<?php

namespace App\Filament\Pages;

use App\Models\Sede;
use App\Services\ReportService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports';

    public ?array $filterData = [];

    public array $reportData = [];

    public bool $showReport = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('filterData.sede_id')
                    ->label('Sede')
                    ->placeholder('Todas las sedes')
                    ->options(fn () => Sede::orderBy('nombre')->pluck('nombre', 'id'))
                    ->native(false),
                DatePicker::make('filterData.date_from')
                    ->label('Desde')
                    ->native(false),
                DatePicker::make('filterData.date_to')
                    ->label('Hasta')
                    ->native(false),
                Select::make('filterData.option_type')
                    ->label('Opción a calificar')
                    ->placeholder('Todas')
                    ->options([
                        'Queja' => 'Queja',
                        'Reclamo' => 'Reclamo',
                        'Petición' => 'Petición',
                        'Sugerencia' => 'Sugerencia',
                        'Felicitación' => 'Felicitación',
                    ])
                    ->native(false),
                Select::make('filterData.rating_category')
                    ->label('Categoría de calificación')
                    ->placeholder('Todas')
                    ->options([
                        'ambientacion' => 'Ambientación',
                        'atencion' => 'Atención a la Mesa',
                        'comida' => 'Calidad de la Comida',
                        'tiempo' => 'Tiempo de Entrega',
                    ])
                    ->native(false),
            ])
            ->columns([
                'default' => 1,
                'md' => 2,
                'xl' => 3,
            ]);
    }

    public function generateReport(): void
    {
        $state = $this->form->getState();
        $f = $state['filterData'] ?? [];

        if (! $this->dateRangeIsValid($f)) {
            return;
        }

        $service = ReportService::make(
            sedeId: $f['sede_id'] ?? null,
            dateFrom: $f['date_from'] ?? null,
            dateTo: $f['date_to'] ?? null,
            optionType: $f['option_type'] ?? null,
            ratingCategory: $f['rating_category'] ?? null,
        );

        $this->reportData = $service->getAll();
        $this->showReport = true;
    }

    public function getDownloadUrl(string $format): string
    {
        $state = $this->form->getState();
        $f = $state['filterData'] ?? [];

        $params = array_filter([
            'sede_id' => $f['sede_id'] ?? null,
            'date_from' => $f['date_from'] ?? null,
            'date_to' => $f['date_to'] ?? null,
            'option_type' => $f['option_type'] ?? null,
            'rating_category' => $f['rating_category'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        return route("admin.reportes.{$format}", $params);
    }

    protected function dateRangeIsValid(array $filters): bool
    {
        $from = Carbon::parse($filters['date_from'] ?? now()->subDays(30)->toDateString());
        $to = Carbon::parse($filters['date_to'] ?? now()->toDateString());

        if ($to->lt($from)) {
            Notification::make()
                ->title('Rango de fechas inválido')
                ->body('La fecha final debe ser mayor o igual a la fecha inicial.')
                ->danger()
                ->send();

            return false;
        }

        if ($from->diffInDays($to) > 366) {
            Notification::make()
                ->title('Rango de fechas demasiado amplio')
                ->body('Seleccione un periodo máximo de 1 año.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generar reporte')
                ->icon('heroicon-o-document-chart-bar')
                ->color('primary')
                ->action('generateReport'),
        ];
    }
}
