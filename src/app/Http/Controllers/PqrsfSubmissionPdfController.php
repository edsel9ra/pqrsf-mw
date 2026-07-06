<?php

namespace App\Http\Controllers;

use App\Models\PqrsfSubmission;
use Barryvdh\DomPDF\Facade\Pdf;

class PqrsfSubmissionPdfController extends Controller
{
    public function show(PqrsfSubmission $submission)
    {
        $submission->load('sede');

        $pdf = Pdf::loadView('emails.pqrsf-submission-pdf', [
            'submission' => $submission,
            'logoSrc' => $this->logoDataUri(),
        ]);

        $pdf->setPaper('letter');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isJavascriptEnabled' => false,
            'dpi' => 150,
            'fontHeightRatio' => 1.05,
        ]);

        return $pdf->stream('pqrsf-'.$submission->id.'.pdf');
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
