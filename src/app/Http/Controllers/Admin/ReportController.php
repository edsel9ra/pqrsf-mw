<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportFilters;
use App\Services\ReportPdfService;
use App\Services\Reports\ObservationsReportService;
use App\Services\Reports\ReportPdfService as DetailedReportPdfService;
use App\Services\Reports\ReportXlsxService;
use App\Services\Reports\SubmissionReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function pdf(Request $request, ReportPdfService $reportPdfService)
    {
        $filters = $this->getValidatedFilters($request);
        $report = $reportPdfService->generate($filters);

        return response($report['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$report['filename'].'"',
        ]);
    }

    public function submissionsPdf(
        Request $request,
        SubmissionReportService $reportService,
        DetailedReportPdfService $pdfService,
    ) {
        $filters = $this->getValidatedDetailFilters($request);
        $report = $pdfService->generate(
            'reports.submission-report-pdf',
            $reportService->getData($filters),
            $this->filename('registros', 'pdf'),
        );

        return $this->binaryDownload($report, 'application/pdf');
    }

    public function submissionsXlsx(
        Request $request,
        SubmissionReportService $reportService,
        ReportXlsxService $xlsxService,
    ) {
        $filters = $this->getValidatedDetailFilters($request);
        $data = $reportService->getData($filters);
        $rows = $data['rows']->map(fn (array $row): array => [
            $row['fecha'],
            $row['sede'],
            $row['nombre_completo'],
            $row['nombre_mesero'],
        ]);
        $report = $xlsxService->generate(
            ['Fecha', 'Sede', 'Nombre Completo', 'Nombre de Mesero'],
            $rows,
            $this->filename('registros', 'xlsx'),
        );

        return $this->binaryDownload(
            $report,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }

    public function observationsPdf(
        Request $request,
        ObservationsReportService $reportService,
        DetailedReportPdfService $pdfService,
    ) {
        $filters = $this->getValidatedDetailFilters($request);
        $report = $pdfService->generate(
            'reports.observations-report-pdf',
            $reportService->getData($filters),
            $this->filename('observaciones', 'pdf'),
        );

        return $this->binaryDownload($report, 'application/pdf');
    }

    public function observationsXlsx(
        Request $request,
        ObservationsReportService $reportService,
        ReportXlsxService $xlsxService,
    ) {
        $filters = $this->getValidatedDetailFilters($request);
        $data = $reportService->getData($filters);
        $rows = $data['groups']
            ->flatMap(fn (array $group) => $group['rows'])
            ->map(fn (array $row): array => [
                $row['sede'],
                $row['nombre_completo'],
                $row['opcion_calificada'],
                $row['observaciones'],
                $row['nombre_mesero'],
            ]);
        $report = $xlsxService->generate(
            ['Sede', 'Nombre Completo', 'Opción Calificada', 'Observaciones', 'Nombre de Mesero'],
            $rows,
            $this->filename('observaciones', 'xlsx'),
        );

        return $this->binaryDownload(
            $report,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }

    protected function getValidatedFilters(Request $request): array
    {
        if ($request->filled('sede_id') && ! is_array($request->input('sede_id'))) {
            $request->merge(['sede_id' => [$request->input('sede_id')]]);
        }

        $validated = $request->validate([
            'sede_id' => ['nullable', 'array'],
            'sede_id.*' => ['integer', 'distinct', 'exists:sedes,id'],
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'option_type' => 'nullable|string|in:Queja,Reclamo,Petición,Sugerencia,Felicitación',
            'rating_category' => 'nullable|string|in:ambientacion,atencion,comida,tiempo',
        ]);

        $normalized = ReportFilters::normalize($validated);

        foreach (['sede_id', 'date_from', 'date_to'] as $filter) {
            if (array_key_exists($filter, $validated)) {
                $validated[$filter] = $normalized[$filter];
            }
        }

        $this->validateDateRange($validated);

        return $validated;
    }

    protected function getValidatedDetailFilters(Request $request): array
    {
        return ReportFilters::validate($request->all());
    }

    private function binaryDownload(array $report, string $contentType)
    {
        return response($report['content'], 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$report['filename'].'"',
        ]);
    }

    private function filename(string $type, string $extension): string
    {
        return 'reporte-'.$type.'-pqrsf-'.now()->format('Y-m-d').'.'.$extension;
    }

    protected function validateDateRange(array $filters): void
    {
        $from = Carbon::parse($filters['date_from'] ?? now()->subDays(30)->toDateString());
        $to = Carbon::parse($filters['date_to'] ?? now()->toDateString());

        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages([
                'date_from' => 'Seleccione un periodo máximo de 1 año.',
            ]);
        }
    }
}
