<?php

namespace App\Filament\Pages;

use App\Services\PqrsfCsvImportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class ImportPqrsfCsv extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Importar CSV';

    protected static ?string $title = 'Importar PQRSF desde CSV';

    protected static string|UnitEnum|null $navigationGroup = 'PQRSF';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.import-pqrsf-csv';

    public ?array $data = [];

    public ?array $summary = null;

    public bool $readyToImport = false;

    public function mount(): void
    {
        $this->form->fill([
            'status' => 'pending',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                FileUpload::make('csv_file')
                    ->label('Archivo CSV')
                    ->helperText('Use el archivo exportado desde Microsoft Forms. El analisis no guarda registros.')
                    ->disk('local')
                    ->directory('pqrsf-imports')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'application/vnd.ms-excel',
                        'text/comma-separated-values',
                    ])
                    ->maxSize(20480)
                    ->required()
                    ->previewable(false)
                    ->openable(false)
                    ->downloadable(false),
                Select::make('status')
                    ->label('Estado inicial')
                    ->options([
                        'pending' => 'Pendiente',
                        'validated' => 'Validado',
                        'sent' => 'Enviado',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false),
            ])
            ->statePath('data')
            ->columns([
                'default' => 1,
                'lg' => 2,
            ]);
    }

    public function analyzeCsv(): void
    {
        $path = $this->uploadedCsvPath();

        if ($path === null) {
            return;
        }

        $this->summary = app(PqrsfCsvImportService::class)->analyze($path);
        $this->readyToImport = $this->summary['errors'] === [] && $this->summary['new_count'] > 0;

        if ($this->summary['errors'] !== []) {
            Notification::make()
                ->title('El CSV tiene errores')
                ->body('Revise el resumen antes de intentar importar.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Analisis completado')
            ->body($this->summary['new_count'].' registros nuevos encontrados.')
            ->success()
            ->send();
    }

    public function confirmImport(): void
    {
        $path = $this->uploadedCsvPath();

        if ($path === null) {
            return;
        }

        $status = (string) ($this->data['status'] ?? 'pending');

        $this->summary = app(PqrsfCsvImportService::class)->import($path, $status);
        $this->readyToImport = false;

        if ($this->summary['errors'] !== []) {
            Notification::make()
                ->title('El CSV tiene errores')
                ->body('No se importaron registros.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Importacion completada')
            ->body($this->summary['imported'].' registros importados y '.$this->summary['created_sedes'].' sedes creadas.')
            ->success()
            ->send();
    }

    public function updatedData(): void
    {
        $this->summary = null;
        $this->readyToImport = false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('analyze')
                ->label('Analizar CSV')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('gray')
                ->action('analyzeCsv'),
            Action::make('import')
                ->label('Importar registros')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Importar registros nuevos')
                ->modalDescription('Se crearan las sedes faltantes y se omitiran los CSV ID ya importados.')
                ->visible(fn (): bool => $this->readyToImport)
                ->action('confirmImport'),
        ];
    }

    private function uploadedCsvPath(): ?string
    {
        $state = $this->form->getState();
        $file = $state['csv_file'] ?? null;

        if (is_array($file)) {
            $file = reset($file) ?: null;
        }

        if ($file instanceof TemporaryUploadedFile) {
            return $file->getRealPath() ?: null;
        }

        if (is_string($file) && $file !== '') {
            $disk = Storage::disk('local');

            if ($disk->exists($file)) {
                return $disk->path($file);
            }
        }

        Notification::make()
            ->title('Seleccione un archivo CSV')
            ->body('Debe cargar un archivo antes de analizar o importar.')
            ->danger()
            ->send();

        return null;
    }
}
