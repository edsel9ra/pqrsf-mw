<?php

namespace App\Services;

use App\Mail\PqrsfSubmissionMail;
use App\Models\PqrsfSubmission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PqrsfComplaintNotificationService
{
    public function sendIfComplaint(PqrsfSubmission $submission): void
    {
        if (($submission->field_values['opcion_a_calificar'] ?? null) !== 'Queja') {
            return;
        }

        $recipients = $submission->sede?->complaintRecipients()
            ->where('activo', true)
            ->get();

        if ($recipients === null || $recipients->isEmpty()) {
            Log::warning('No hay destinatarios de quejas configurados para la sede.', [
                'submission_id' => $submission->id,
                'sede_id' => $submission->sede_id,
            ]);

            return;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email, $recipient->nombre)
                    ->send(new PqrsfSubmissionMail($submission));
            } catch (Throwable $exception) {
                report($exception);

                Log::error('No se pudo notificar una queja al destinatario configurado.', [
                    'submission_id' => $submission->id,
                    'sede_id' => $submission->sede_id,
                    'email' => $recipient->email,
                    'exception' => $exception,
                ]);
            }
        }
    }
}
