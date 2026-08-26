<x-mail::message>
# Reporte PQRSF

Se adjunta el reporte consolidado correspondiente a **{{ $scopeLabel }}**.

**Generado:** {{ $generatedAt }}

@if (! empty($filterLabels))
**Filtros aplicados:** {{ implode(' | ', $filterLabels) }}
@endif

El archivo PDF contiene el resumen y los indicadores del periodo seleccionado.

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
