<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function pdf(Request $request)
    {
        $data = $this->getReportData($request);

        $pdf = Pdf::loadView('reports.pdf', $data);
        $pdf->setPaper('letter', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isJavascriptEnabled' => true,
            'dpi' => 150,
            'fontHeightRatio' => 1.1,
        ]);

        return $pdf->download('reporte-pqrsf-'.now()->format('Y-m-d').'.pdf');
    }

    protected function getReportData(Request $request): array
    {
        $validated = $request->validate([
            'sede_id' => 'nullable|integer|exists:sedes,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'option_type' => 'nullable|string|in:Queja,Reclamo,Petición,Sugerencia,Felicitación',
            'rating_category' => 'nullable|string|in:ambientacion,atencion,comida,tiempo',
        ]);

        $this->validateDateRange($validated);

        $service = ReportService::make(
            sedeId: $validated['sede_id'] ?? null,
            dateFrom: $validated['date_from'] ?? null,
            dateTo: $validated['date_to'] ?? null,
            optionType: $validated['option_type'] ?? null,
            ratingCategory: $validated['rating_category'] ?? null,
        );

        return [
            ...$service->getAll(),
            'logoSrc' => $this->logoDataUri(),
        ];
    }

    private function logoDataUri(): string
    {
        $path = public_path('logo_mw.png');

        if (! is_file($path)) {
            return '';
        }

        return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
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
