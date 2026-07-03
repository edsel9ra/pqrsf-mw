<x-mail::message>
@include('emails.partials.pqrsf-summary', ['submission' => $submission])

<x-mail::button :url="\Illuminate\Support\Facades\URL::signedRoute('pqrsf.submissions.pdf', ['submission' => $submission])">
Abrir PDF
</x-mail::button>

<p style="margin-top: 26px; color: #7c6f64; font-size: 13px; line-height: 1.6;">Este correo fue generado automáticamente por {{ config('app.name') }} para facilitar el seguimiento interno de la PQRSF.</p>
</x-mail::message>
