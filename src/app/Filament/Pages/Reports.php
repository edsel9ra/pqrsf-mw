<?php

namespace App\Filament\Pages;

use App\Mail\ReportPdfMail;
use App\Models\Sede;
use App\Services\ReportFilters;
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
use Illuminate\Support\Arr;
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

    public static function canAccess(): bool
    {
        return auth()->user()?->canAccessReadOnlyPanel() ?? false;
    }

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
                    ->multiple()
                    ->live()
                    ->searchable()
                    ->preload()
                    ->native(false),
                DatePicker::make('filterData.date_from')
                    ->label('Desde')
                    ->live()
                    ->native(false),
                DatePicker::make('filterData.date_to')
                    ->label('Hasta')
                    ->live()
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
        $rawFilters = $this->getFormFilters();

        if (! $this->filtersAreValid($rawFilters)) {
            return;
        }

        $filters = $this->normalizeFilters($rawFilters);
        $this->appliedFilters = $filters;
        $this->reportData = app(ReportPdfService::class)->getData($filters);
        $this->showReport = true;
    }

    public function sendReport(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (! $this->showReport || $this->appliedFilters === []) {
            Notification::make()
                ->title('Genere el reporte antes de enviarlo')
                ->danger()
                ->send();

            return;
        }

        $rawFilters = $this->prepareFilters($this->appliedFilters);

        if (! $this->filtersAreValid($rawFilters)) {
            return;
        }

        $filters = $this->normalizeFilters($rawFilters);
        $this->appliedFilters = $filters;
        $sedeIds = $filters['sede_id'];

        if ($sedeIds === null) {
            Notification::make()
                ->title('Seleccione al menos una sede para enviar el reporte')
                ->body('Un reporte de todas las sedes no tiene un grupo único de destinatarios.')
                ->danger()
                ->send();

            return;
        }

        $sedes = Sede::query()
            ->whereIn('id', $sedeIds)
            ->with(['sedeRecipients' => fn ($query) => $query->where('activo', true)->orderBy('id')])
            ->orderBy('nombre')
            ->get();

        if ($sedes->count() !== count($sedeIds)) {
            Notification::make()
                ->title('Una o más sedes seleccionadas no están disponibles')
                ->danger()
                ->send();

            return;
        }

        $sedesWithoutRecipients = $sedes
            ->filter(fn (Sede $sede): bool => $sede->sedeRecipients->isEmpty())
            ->pluck('nombre');

        $sedesWithInvalidRecipients = $sedes
            ->flatMap(fn (Sede $sede) => $sede->sedeRecipients
                ->filter(fn ($recipient): bool => trim((string) $recipient->email) === '')
                ->map(fn () => $sede->nombre))
            ->unique()
            ->values();

        if ($sedesWithoutRecipients->isNotEmpty()) {
            Notification::make()
                ->title('No hay destinatarios activos para todas las sedes')
                ->body('Configure destinatarios activos para: '.$sedesWithoutRecipients->implode(', ').'.')
                ->danger()
                ->send();

            return;
        }

        if ($sedesWithInvalidRecipients->isNotEmpty()) {
            Notification::make()
                ->title('Hay destinatarios activos sin correo electrónico')
                ->body('Corrija los destinatarios de: '.$sedesWithInvalidRecipients->implode(', ').'.')
                ->danger()
                ->send();

            return;
        }

        $recipientGroups = [];
        foreach ($sedes as $sede) {
            foreach ($sede->sedeRecipients as $recipient) {
                $email = strtolower(trim((string) $recipient->email));

                if ($email === '') {
                    continue;
                }

                $recipientGroups[$email] ??= [
                    'email' => trim((string) $recipient->email),
                    'name' => (string) $recipient->nombre,
                    'sede_ids' => [],
                    'sede_names' => [],
                ];

                if (! in_array($sede->id, $recipientGroups[$email]['sede_ids'], true)) {
                    $recipientGroups[$email]['sede_ids'][] = $sede->id;
                    $recipientGroups[$email]['sede_names'][] = $sede->nombre;
                }
            }
        }

        if ($recipientGroups === []) {
            Notification::make()
                ->title('No hay destinatarios activos para las sedes seleccionadas')
                ->body('Configure al menos un destinatario activo antes de enviar el reporte.')
                ->danger()
                ->send();

            return;
        }

        $reports = [];
        $sent = [];
        $failed = [];

        foreach ($recipientGroups as $group) {
            try {
                sort($group['sede_ids'], SORT_NUMERIC);
                $scopeKey = implode(',', $group['sede_ids']);

                if (! array_key_exists($scopeKey, $reports)) {
                    $reports[$scopeKey] = app(ReportPdfService::class)->generate([
                        ...$filters,
                        'sede_id' => $group['sede_ids'],
                    ]);
                }

                $report = $reports[$scopeKey];
                $scopeLabel = count($group['sede_names']) === 1
                    ? 'Sede: '.$group['sede_names'][0]
                    : 'Sedes: '.implode(', ', $group['sede_names']);

                Mail::to($group['email'], $group['name'])
                    ->send(new ReportPdfMail(
                        pdfContent: $report['content'],
                        filename: $report['filename'],
                        scopeLabel: $scopeLabel,
                        filterLabels: $report['data']['filterLabels'],
                        generatedAt: $report['data']['generatedAt'],
                    ));

                $sent[] = $group['email'];
            } catch (Throwable $exception) {
                report($exception);
                $failed[] = $group['email'];
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
            ->body('Se envió a '.count($sent).' destinatario(s) de '.count($sedeIds).' sede(s).')
            ->success()
            ->send();
    }

    public function getDownloadUrl(string $format): string
    {
        $filters = $this->reportData['filters'] ?? [];

        if (! is_array($filters) || $filters === []) {
            $filters = $this->appliedFilters;
        }

        return route(
            "admin.reportes.{$format}",
            ReportFilters::queryParams($filters),
        );
    }

    protected function getFormFilters(): array
    {
        $state = $this->form->getState();
        $filters = $state['filterData'] ?? [];

        return $this->prepareFilters([
            'sede_id' => $filters['sede_id'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'option_type' => $filters['option_type'] ?? null,
            'rating_category' => $filters['rating_category'] ?? null,
        ]);
    }

    protected function filtersAreValid(array $filters): bool
    {
        $validator = Validator::make($filters, [
            'sede_id' => ['nullable', 'array'],
            'sede_id.*' => ['integer', 'distinct', 'exists:sedes,id'],
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

    protected function normalizeFilters(array $filters): array
    {
        $normalized = ReportFilters::normalize($filters);

        return [
            'sede_id' => $normalized['sede_id'],
            'date_from' => $normalized['date_from'],
            'date_to' => $normalized['date_to'],
            'option_type' => $filters['option_type'] ?? null,
            'rating_category' => $filters['rating_category'] ?? null,
        ];
    }

    protected function prepareFilters(array $filters): array
    {
        return [
            'sede_id' => $this->wrapSedeIds($filters['sede_id'] ?? null),
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'option_type' => $filters['option_type'] ?? null,
            'rating_category' => $filters['rating_category'] ?? null,
        ];
    }

    protected function wrapSedeIds(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Arr::wrap($value);
    }

    protected function normalizeSedeIds(mixed $value): ?array
    {
        $ids = collect(Arr::wrap($value))
            ->filter(fn ($sedeId): bool => filled($sedeId))
            ->map(fn ($sedeId): int => (int) $sedeId)
            ->unique()
            ->values()
            ->all();

        return $ids !== [] ? $ids : null;
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
