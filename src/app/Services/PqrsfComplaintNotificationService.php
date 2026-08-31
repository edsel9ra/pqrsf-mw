<?php

namespace App\Services;

use App\Mail\PqrsfSubmissionMail;
use App\Models\ComplaintRecipientProfile;
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

        $recipientEmails = $recipients->pluck('email')
            ->map(fn (string $email): ?string => ComplaintRecipientProfile::normalizeEmail($email))
            ->filter()
            ->unique()
            ->values();
        $profiles = $recipientEmails->isEmpty()
            ? collect()
            : ComplaintRecipientProfile::query()
                ->whereIn('email', $recipientEmails->all())
                ->get()
                ->keyBy('email');

        foreach ($recipients as $recipient) {
            try {
                $profile = $profiles->get(ComplaintRecipientProfile::normalizeEmail($recipient->email));
                $excludedFieldKeys = $profile?->effectiveExcludedFieldKeys() ?? [];

                Mail::to($recipient->email, $recipient->nombre)
                    ->send(new PqrsfSubmissionMail(
                        $submission,
                        $excludedFieldKeys,
                        $excludedFieldKeys === [],
                    ));
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
