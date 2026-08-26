<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportPdfService
{
    public function generate(string $view, array $data, string $filename): array
    {
        $pdf = Pdf::loadView($view, [
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
            'filename' => $filename,
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
