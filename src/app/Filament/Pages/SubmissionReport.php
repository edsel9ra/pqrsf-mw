<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasReportFilters;
use App\Services\Reports\SubmissionReportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SubmissionReport extends Page implements HasForms
{
    use HasReportFilters;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Reporte de registros';

    protected static ?string $title = 'Reporte de registros PQRSF';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.submission-report';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $this->reportForm($form);
    }

    public function generateReport(): void
    {
        $filters = $this->validatedReportFilters();

        if ($filters === null) {
            return;
        }

        $this->appliedFilters = $filters;
        $this->reportData = app(SubmissionReportService::class)->getData($filters);
        $this->showReport = true;
    }

    protected function reportRouteName(): string
    {
        return 'admin.reportes.registros';
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
