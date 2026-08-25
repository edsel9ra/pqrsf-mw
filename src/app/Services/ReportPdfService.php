<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportPdfService
{
    public function getData(array $filters): array
    {
        $service = ReportService::make(
            sedeId: $filters['sede_id'] ?? null,
            dateFrom: $filters['date_from'] ?? null,
            dateTo: $filters['date_to'] ?? null,
            optionType: $filters['option_type'] ?? null,
            ratingCategory: $filters['rating_category'] ?? null,
        );

        return $service->getAll();
    }

    public function generate(array $filters, ?array $data = null): array
    {
        $data ??= $this->getData($filters);

        $pdf = Pdf::loadView('reports.pdf', [
            ...$data,
            'logoSrc' => $this->logoDataUri(),
        ]);

        $pdf->setPaper('letter', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isJavascriptEnabled' => false,
            'dpi' => 150,
            'fontHeightRatio' => 1.05,
        ]);

        return [
            'content' => $pdf->output(),
            'data' => $data,
            'filename' => 'reporte-pqrsf-'.now()->format('Y-m-d').'.pdf',
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
}
