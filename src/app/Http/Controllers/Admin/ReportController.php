<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportPdfService;
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

    protected function getValidatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'sede_id' => 'nullable|integer|exists:sedes,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'option_type' => 'nullable|string|in:Queja,Reclamo,Petición,Sugerencia,Felicitación',
            'rating_category' => 'nullable|string|in:ambientacion,atencion,comida,tiempo',
        ]);

        $this->validateDateRange($validated);

        return $validated;
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
