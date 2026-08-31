<?php

namespace App\Mail;

use App\Models\PqrsfSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PqrsfSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PqrsfSubmission $submission,
        public array $excludedFieldKeys = [],
        public bool $includePdf = true,
    ) {
        $this->excludedFieldKeys = collect($excludedFieldKeys)
            ->filter(fn (mixed $key): bool => is_string($key) && $key !== '')
            ->unique()
            ->values()
            ->all();
        $this->includePdf = $includePdf && $this->excludedFieldKeys === [];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva PQRSF - '.($this->submission->field_values['opcion_a_calificar'] ?? 'Formulario'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pqrsf-submission',
            with: [
                'submission' => $this->submission,
                'excludedFieldKeys' => $this->excludedFieldKeys,
                'includePdf' => $this->includePdf,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
