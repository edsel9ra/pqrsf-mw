<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $pdfContent,
        public string $filename,
        public string $scopeLabel,
        public array $filterLabels,
        public string $generatedAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reporte PQRSF - '.$this->scopeLabel,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.report-pdf',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn (): string => $this->pdfContent,
                $this->filename,
            )->withMime('application/pdf'),
        ];
    }
}
