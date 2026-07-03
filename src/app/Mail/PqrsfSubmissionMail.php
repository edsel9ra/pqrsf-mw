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

    public PqrsfSubmission $submission;

    public function __construct(PqrsfSubmission $submission)
    {
        $this->submission = $submission;
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
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
