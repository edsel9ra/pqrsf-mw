@php
    $values = $submission->field_values ?? [];
    $option = $values['opcion_a_calificar'] ?? 'Sin clasificar';
    $optionColors = match ($option) {
        'Queja' => ['background' => '#fee2e2', 'border' => '#fecaca', 'text' => '#991b1b'],
        'Reclamo' => ['background' => '#ffedd5', 'border' => '#fed7aa', 'text' => '#9a3412'],
        'Petición' => ['background' => '#dbeafe', 'border' => '#bfdbfe', 'text' => '#1e40af'],
        'Sugerencia' => ['background' => '#f1f5f9', 'border' => '#cbd5e1', 'text' => '#334155'],
        'Felicitación' => ['background' => '#dcfce7', 'border' => '#bbf7d0', 'text' => '#166534'],
        default => ['background' => '#f8fafc', 'border' => '#e2e8f0', 'text' => '#334155'],
    };
    $ratings = [
        'Ambientación' => $values['calificacion_ambientacion'] ?? null,
        'Atención' => $values['calificacion_atencion'] ?? null,
        'Comida' => $values['calificacion_comida'] ?? null,
        'Tiempo' => $values['calificacion_tiempo'] ?? null,
    ];
    $numericRatings = collect($ratings)->filter(fn ($rating): bool => is_numeric($rating));
    $average = $numericRatings->isEmpty()
        ? '—'
        : number_format($numericRatings->avg(), 1, ',', '.');
    $recommendation = array_key_exists('recomendaria', $values)
        ? ($values['recomendaria'] ? 'Sí' : 'No')
        : '—';
    $medium = is_array($values['medio_conocimiento'] ?? null)
        ? implode(', ', array_filter($values['medio_conocimiento']))
        : ($values['medio_conocimiento'] ?? '—');
    $observations = filled($values['observaciones'] ?? null)
        ? $values['observaciones']
        : 'Sin observaciones registradas.';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0 0 24px;">
<tr>
<td style="padding: 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; overflow: hidden; border-radius: 22px; background: #14110f;">
<tr>
<td style="padding: 28px 30px 26px; background: linear-gradient(135deg, #14110f 0%, #24180f 58%, #8a4b13 140%); color: #ffffff;">
<div style="margin: 0 0 12px; color: #f6b84b; font-size: 11px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase;">Nuevo expediente PQRSF</div>
<h1 style="margin: 0; color: #ffffff; font-size: 30px; font-weight: 900; line-height: 1.05; letter-spacing: -0.04em;">Solicitud #{{ $submission->id }}</h1>
<p style="margin: 12px 0 0; color: #f8e4c4; font-size: 15px; line-height: 1.6;">Se registró una nueva interacción de cliente para seguimiento administrativo.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0 0 22px;">
<tr>
<td width="50%" style="padding: 0 7px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border: 1px solid #eadfce; border-radius: 16px; background: #fffaf2;">
<tr>
<td style="padding: 18px;">
<div style="margin: 0 0 6px; color: #9a6a21; font-size: 11px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;">Sede</div>
<div style="color: #231815; font-size: 17px; font-weight: 850; line-height: 1.35;">{{ $submission->sede?->nombre ?? '—' }}</div>
</td>
</tr>
</table>
</td>
<td width="50%" style="padding: 0 0 0 7px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border: 1px solid #eadfce; border-radius: 16px; background: #fffaf2;">
<tr>
<td style="padding: 18px;">
<div style="margin: 0 0 6px; color: #9a6a21; font-size: 11px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;">Fecha de registro</div>
<div style="color: #231815; font-size: 17px; font-weight: 850; line-height: 1.35;">{{ $submission->created_at->format('d/m/Y H:i') }}</div>
</td>
</tr>
</table>
</td>
</tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0 0 22px; border: 1px solid #e7ded1; border-radius: 18px; background: #ffffff;">
<tr>
<td style="padding: 22px 24px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
<tr>
<td style="padding: 0 0 16px;">
<div style="color: #9a6a21; font-size: 11px; font-weight: 900; letter-spacing: 0.14em; text-transform: uppercase;">Resumen</div>
<h2 style="margin: 7px 0 0; color: #1f1714; font-size: 20px; font-weight: 900; line-height: 1.2;">{{ $values['nombre_completo'] ?? 'Cliente sin nombre' }}</h2>
</td>
<td align="right" style="padding: 0 0 16px;">
<span style="display: inline-block; padding: 7px 12px; border: 1px solid {{ $optionColors['border'] }}; border-radius: 999px; background: {{ $optionColors['background'] }}; color: {{ $optionColors['text'] }}; font-size: 12px; font-weight: 800;">{{ $option }}</span>
</td>
</tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
<tr>
<td style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #7c6f64; font-size: 13px; font-weight: 700;">Número móvil</td>
<td align="right" style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #221714; font-size: 14px; font-weight: 750;">{{ $values['numero_movil'] ?? '—' }}</td>
</tr>
<tr>
<td style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #7c6f64; font-size: 13px; font-weight: 700;">Correo electrónico</td>
<td align="right" style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #221714; font-size: 14px; font-weight: 750;">{{ $values['correo_electronico'] ?? '—' }}</td>
</tr>
<tr>
<td style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #7c6f64; font-size: 13px; font-weight: 700;">Mesero</td>
<td align="right" style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #221714; font-size: 14px; font-weight: 750;">{{ $values['nombre_mesero'] ?? '—' }}</td>
</tr>
<tr>
<td style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #7c6f64; font-size: 13px; font-weight: 700;">¿Recomendaría?</td>
<td align="right" style="padding: 12px 0; border-top: 1px solid #f0e6d8; color: #221714; font-size: 14px; font-weight: 750;">{{ $recommendation }}</td>
</tr>
<tr>
<td style="padding: 12px 0 0; border-top: 1px solid #f0e6d8; color: #7c6f64; font-size: 13px; font-weight: 700;">Medio de conocimiento</td>
<td align="right" style="padding: 12px 0 0; border-top: 1px solid #f0e6d8; color: #221714; font-size: 14px; font-weight: 750;">{{ $medium ?: '—' }}</td>
</tr>
</table>
</td>
</tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0 0 22px;">
<tr>
<td style="padding: 0;">
<div style="margin: 0 0 10px; color: #9a6a21; font-size: 11px; font-weight: 900; letter-spacing: 0.14em; text-transform: uppercase;">Calificaciones</div>
</td>
</tr>
<tr>
@foreach ($ratings as $label => $rating)
<td width="25%" style="padding: 0 {{ $loop->last ? '0' : '6px' }} 0 {{ $loop->first ? '0' : '6px' }};">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-radius: 15px; background: #17120f;">
<tr>
<td style="padding: 16px 12px; text-align: center;">
<div style="color: #f4b24e; font-size: 25px; font-weight: 900; line-height: 1;">{{ is_numeric($rating) ? $rating : '—' }}</div>
<div style="margin-top: 7px; color: #f5e7d0; font-size: 11px; font-weight: 750; line-height: 1.25;">{{ $label }}</div>
</td>
</tr>
</table>
</td>
@endforeach
</tr>
<tr>
<td colspan="4" style="padding: 14px 0 0; color: #6f6258; font-size: 13px; line-height: 1.55;">Promedio general: <strong style="color: #1f1714;">{{ $average }}/5</strong></td>
</tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0 0 24px; border-radius: 18px; background: #f6efe5; border: 1px solid #eadfce;">
<tr>
<td style="padding: 22px 24px;">
<div style="margin: 0 0 9px; color: #9a6a21; font-size: 11px; font-weight: 900; letter-spacing: 0.14em; text-transform: uppercase;">Observaciones del cliente</div>
<p style="margin: 0; color: #2a211d; font-size: 15px; line-height: 1.7;">{{ $observations }}</p>
</td>
</tr>
</table>
