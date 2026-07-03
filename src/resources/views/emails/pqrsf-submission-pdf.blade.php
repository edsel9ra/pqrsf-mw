<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PQRSF #{{ $submission->id }}</title>
    <style>
        @page { margin: 24px; }
        body {
            margin: 0;
            background: #f4efe8;
            color: #231815;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .document {
            width: 100%;
            margin: 0 auto;
        }
        table { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="document">
        @include('emails.partials.pqrsf-summary', ['submission' => $submission])

        <p style="margin-top: 18px; color: #7c6f64; font-size: 11px; line-height: 1.6;">
            Documento generado automáticamente por {{ config('app.name') }} para seguimiento interno de la PQRSF.
        </p>
    </div>
</body>
</html>
