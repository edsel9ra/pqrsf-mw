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
}
