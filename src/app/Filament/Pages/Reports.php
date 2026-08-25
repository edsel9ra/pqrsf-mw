<?php

namespace App\Filament\Pages;

use App\Mail\ReportPdfMail;
use App\Models\Sede;
use App\Services\ReportPdfService;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;
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

    public array $appliedFilters = [];

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
        $filters = $this->getFormFilters();

        if (! $this->filtersAreValid($filters)) {
            return;
        }

        $this->appliedFilters = $filters;
        $this->reportData = app(ReportPdfService::class)->getData($filters);
        $this->showReport = true;
    }

    public function sendReport(): void
    {
        if (! $this->showReport || $this->appliedFilters === []) {
            Notification::make()
                ->title('Genere el reporte antes de enviarlo')
                ->danger()
                ->send();

            return;
        }

        $sedeId = $this->appliedFilters['sede_id'] ?? null;

        if (! $sedeId) {
            Notification::make()
                ->title('Seleccione una sede para enviar el reporte')
                ->body('Un reporte de todas las sedes no tiene un grupo único de destinatarios.')
                ->danger()
                ->send();

            return;
        }

        $sede = Sede::query()
            ->with(['sedeRecipients' => fn ($query) => $query->where('activo', true)->orderBy('id')])
            ->find($sedeId);

        if (! $sede) {
            Notification::make()
                ->title('La sede seleccionada no está disponible')
                ->danger()
                ->send();

            return;
        }

        $recipients = $sede->sedeRecipients;

        if ($recipients->isEmpty()) {
            Notification::make()
                ->title('No hay destinatarios activos para esta sede')
                ->body('Configure al menos un destinatario activo antes de enviar el reporte.')
                ->danger()
                ->send();

            return;
        }

        try {
            $report = app(ReportPdfService::class)->generate($this->appliedFilters);
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('No se pudo generar el PDF')
                ->body('Intente nuevamente o revise los filtros seleccionados.')
                ->danger()
                ->send();

            return;
        }

        $sent = [];
        $failed = [];

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email, $recipient->nombre)
                    ->send(new ReportPdfMail(
                        pdfContent: $report['content'],
                        filename: $report['filename'],
                        sedeName: $sede->nombre,
                        filterLabels: $report['data']['filterLabels'],
                        generatedAt: $report['data']['generatedAt'],
                    ));

                $sent[] = $recipient->email;
            } catch (Throwable $exception) {
                report($exception);
                $failed[] = $recipient->email;
            }
        }

        if ($failed !== []) {
            Notification::make()
                ->title('El reporte no se envió a todos los destinatarios')
                ->body('Enviados: '.count($sent).'. Fallidos: '.implode(', ', $failed))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Reporte enviado correctamente')
            ->body('Se envió a '.$recipients->count().' destinatario(s) de '.$sede->nombre.'.')
            ->success()
            ->send();
    }

    public function getDownloadUrl(string $format): string
    {
        $params = array_filter(
            $this->appliedFilters,
            fn ($value) => $value !== null && $value !== '',
        );

        return route("admin.reportes.{$format}", $params);
    }

    protected function getFormFilters(): array
    {
        $state = $this->form->getState();
        $filters = $state['filterData'] ?? [];

        return [
            'sede_id' => filled($filters['sede_id'] ?? null) ? (int) $filters['sede_id'] : null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'option_type' => $filters['option_type'] ?? null,
            'rating_category' => $filters['rating_category'] ?? null,
        ];
    }

    protected function filtersAreValid(array $filters): bool
    {
        $validator = Validator::make($filters, [
            'sede_id' => 'nullable|integer|exists:sedes,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'option_type' => 'nullable|string|in:Queja,Reclamo,Petición,Sugerencia,Felicitación',
            'rating_category' => 'nullable|string|in:ambientacion,atencion,comida,tiempo',
        ]);

        if ($validator->fails()) {
            Notification::make()
                ->title('Filtros inválidos')
                ->body($validator->errors()->first())
                ->danger()
                ->send();

            return false;
        }

        return $this->dateRangeIsValid($filters);
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
