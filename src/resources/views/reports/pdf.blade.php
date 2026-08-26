<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte PQRSF</title>
    <style>
        @page {
            margin: 28px 30px 32px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #27313a;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.45;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            margin-bottom: 16px;
            padding: 17px 20px 16px;
            border-left: 6px solid #d49a3a;
            background: #211a17;
            color: #ffffff;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-content {
            padding-right: 18px;
        }

        .header-eyebrow,
        .section-eyebrow {
            margin: 0 0 5px;
            color: #dca855;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: -0.3px;
            line-height: 1.1;
        }

        .header-description {
            margin: 6px 0 0;
            color: #eadfce;
            font-size: 9px;
        }

        .header-side {
            width: 130px;
            text-align: right;
        }

        .header-side img {
            width: 92px;
            height: auto;
            padding: 7px;
            background: #ffffff;
        }

        .header-meta {
            margin-top: 10px;
            color: #c9b9a7;
            font-size: 7.5px;
        }

        .filter-list {
            margin-top: 8px;
        }

        .filter-tag {
            display: inline-block;
            margin: 0 4px 4px 0;
            padding: 3px 6px;
            border: 1px solid #59483a;
            color: #f4dfb8;
            font-size: 7px;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            margin: 0 0 7px;
            padding-bottom: 5px;
            border-bottom: 1px solid #d49a3a;
            color: #30231d;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
        }

        .section-title small {
            color: #8b7d70;
            font-size: 7.5px;
            font-weight: normal;
        }

        .metric-grid,
        .rating-grid {
            margin: 0 0 7px;
            border-spacing: 6px 0;
            border-collapse: separate;
        }

        .metric-grid td,
        .rating-grid td {
            padding: 9px 11px;
            border: 1px solid #e2d9cf;
            border-top: 3px solid #877568;
            background: #fbfaf8;
            vertical-align: middle;
        }

        .metric-grid td:first-child,
        .rating-grid td:first-child {
            border-left: 1px solid #e2d9cf;
        }

        .metric-grid td {
            width: 50%;
        }

        .metric-grid .general {
            border-top-color: #426b65;
            background: #f2f7f5;
        }

        .metric-value {
            display: block;
            color: #34424b;
            font-size: 19px;
            font-weight: bold;
            line-height: 1;
        }

        .general .metric-value {
            color: #35645e;
        }

        .metric-label {
            display: block;
            margin-top: 5px;
            color: #75675c;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .rating-grid {
            border-spacing: 5px 0;
        }

        .rating-grid td {
            width: 25%;
            padding: 8px 10px;
            border-top: 2px solid #a79787;
            text-align: center;
        }

        .rating-grid .rating-value {
            display: block;
            font-size: 15px;
            font-weight: bold;
            line-height: 1;
        }

        .rating-grid .rating-label {
            display: block;
            margin-top: 5px;
            color: #75675c;
            font-size: 7px;
        }

        .rating-good { color: #28734f; }
        .rating-mid { color: #b47718; }
        .rating-low { color: #b7483e; }

        .data-table {
            margin-top: 5px;
            font-size: 8px;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table th {
            padding: 6px 7px;
            border-bottom: 2px solid #d49a3a;
            background: #f1ece6;
            color: #594c42;
            font-size: 6.8px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-align: left;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 6px 7px;
            border-bottom: 1px solid #e8e1da;
            color: #35434c;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #fcfbfa;
        }

        .data-table tbody tr {
            page-break-inside: avoid;
        }

        .data-table .total-row td {
            border-top: 2px solid #a79787;
            background: #f4efe9;
            color: #30231d;
            font-weight: bold;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        .score {
            display: inline-block;
            min-width: 30px;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
        }

        .score.good { background: #e1f1e8; color: #28734f; }
        .score.mid { background: #fff1d2; color: #9a6819; }
        .score.low { background: #fbe5e2; color: #a7433a; }

        .bar-track {
            height: 8px;
            background: #e7e1da;
        }

        .bar-fill {
            height: 8px;
        }

        .bar-queja { background: #bd5149; }
        .bar-reclamo { background: #d49a3a; }
        .bar-peticion { background: #527ea1; }
        .bar-sugerencia { background: #7b8790; }
        .bar-felicitacion { background: #4d8a69; }

        .summary-box {
            margin: 7px 0;
            padding: 8px 10px;
            border-left: 3px solid #d49a3a;
            background: #f8f4ee;
            color: #63574d;
            font-size: 8px;
        }

        .summary-box strong {
            color: #30231d;
        }

        .daily-chart {
            margin-top: 8px;
            border-collapse: collapse;
        }

        .daily-chart td {
            padding: 0;
            text-align: center;
            vertical-align: bottom;
        }

        .daily-axis {
            width: 24px;
            padding-right: 5px !important;
            color: #9c9086;
            font-size: 6px;
            text-align: right !important;
        }

        .daily-count {
            height: 12px;
            color: #5c6b73;
            font-size: 6px;
            font-weight: bold;
        }

        .daily-bar-cell {
            height: 68px;
            border-bottom: 1px solid #bfb4a9;
            vertical-align: bottom !important;
        }

        .daily-bar {
            display: block;
            margin: 0 auto;
            min-height: 2px;
            border-radius: 2px 2px 0 0;
        }

        .daily-bar.fill { background: #527ea1; }
        .daily-bar.peak { background: #bd5149; }
        .daily-bar.empty { background: #e0d9d1; }

        .daily-label {
            padding-top: 4px !important;
            color: #80746b;
            font-size: 5.8px;
            white-space: nowrap;
        }

        .footer {
            margin-top: 14px;
            padding-top: 7px;
            border-top: 1px solid #cfc4b9;
            color: #91847a;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>
<body>
<script type="text/php">
    if (isset($pdf)) {
        $font = Font_Metrics::get_font("DejaVu Sans", "normal");
        $pdf->page_text(700, 575, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 7, array(0.45, 0.40, 0.36), 0, 0);
    }
</script>

@php
    $ratingClass = fn ($value) => $value >= 4 ? 'rating-good' : ($value >= 3 ? 'rating-mid' : 'rating-low');
    $scoreClass = fn ($value) => $value >= 4 ? 'good' : ($value >= 3 ? 'mid' : 'low');
    $optionClasses = [
        'Queja' => 'bar-queja',
        'Reclamo' => 'bar-reclamo',
        'Petición' => 'bar-peticion',
        'Sugerencia' => 'bar-sugerencia',
        'Felicitación' => 'bar-felicitacion',
    ];
@endphp

<header class="header">
    <table class="header-table">
        <tr>
            <td class="header-content">
                <p class="header-eyebrow">Panel de análisis y seguimiento</p>
                <h1>Reporte PQRSF</h1>
                <p class="header-description">Resumen consolidado de solicitudes, experiencia y comportamiento del periodo.</p>
                <div class="header-meta">Generado el {{ $generatedAt }}</div>
                <div class="filter-list">
                    @forelse ($filterLabels as $label)
                        <span class="filter-tag">{{ $label }}</span>
                    @empty
                        <span class="filter-tag">Todos los registros disponibles</span>
                    @endforelse
                </div>
            </td>
            @if ($logoSrc)
                <td class="header-side">
                    <img src="{{ $logoSrc }}" alt="Logo MW">
                </td>
            @endif
        </tr>
    </table>
</header>

<section class="section">
    <p class="section-eyebrow">Lectura ejecutiva</p>
    <h2 class="section-title">Resumen general <small>Indicadores principales del reporte</small></h2>

    <table class="metric-grid">
        <tr>
            <td>
                <span class="metric-value">{{ $stats['total'] }}</span>
                <span class="metric-label">Total PQRSF</span>
            </td>
            <td class="general">
                <span class="metric-value">{{ number_format($stats['avg_general'], 1) }}/5</span>
                <span class="metric-label">Promedio general</span>
            </td>
        </tr>
    </table>

    <table class="rating-grid">
        <tr>
            @foreach ([
                ['label' => 'Ambientación', 'value' => $stats['avg_ambientacion']],
                ['label' => 'Atención', 'value' => $stats['avg_atencion']],
                ['label' => 'Comida', 'value' => $stats['avg_comida']],
                ['label' => 'Tiempo', 'value' => $stats['avg_tiempo']],
            ] as $rating)
                <td>
                    <span class="rating-value {{ $ratingClass($rating['value']) }}">{{ number_format($rating['value'], 1) }}</span>
                    <span class="rating-label">{{ $rating['label'] }}</span>
                </td>
            @endforeach
        </tr>
    </table>
</section>

@if ($showRatingComparison ?? false)
    <section class="section">
        <p class="section-eyebrow">Comparativo</p>
        <h2 class="section-title">Calificaciones por sede</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sede</th>
                    <th class="text-center">Ambientación</th>
                    <th class="text-center">Atención</th>
                    <th class="text-center">Comida</th>
                    <th class="text-center">Tiempo</th>
                    <th class="text-center">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ratingsBySede as $sede)
                    <tr>
                        <td><strong>{{ $sede->sede_nombre }}</strong></td>
                        @foreach (['ambientacion', 'atencion', 'comida', 'tiempo'] as $field)
                            <td class="text-center">
                                <span class="score {{ $scoreClass($sede->$field) }}">{{ number_format($sede->$field, 1) }}</span>
                            </td>
                        @endforeach
                        <td class="text-center"><strong>{{ number_format($sede->promedio, 1) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">Sin datos para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endif

@if (! empty($pqrsfBySede) && $pqrsfBySede->isNotEmpty())
    @php $bySedeOptions = ['Felicitación', 'Queja', 'Reclamo', 'Sugerencia', 'Petición']; @endphp
    <section class="section">
        <p class="section-eyebrow">Volumen de solicitudes</p>
        <h2 class="section-title">PQRSF por sede</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sede</th>
                    @foreach ($bySedeOptions as $opt)
                        <th class="text-center">{{ strtoupper($opt) }}</th>
                    @endforeach
                    <th class="text-center">Total general</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pqrsfBySede as $item)
                    <tr class="{{ $item->sede === 'TOTAL GENERAL' ? 'total-row' : '' }}">
                        <td><strong>{{ $item->sede }}</strong></td>
                        @foreach ($bySedeOptions as $opt)
                            <td class="text-center">{{ $item->$opt }}</td>
                        @endforeach
                        <td class="text-center"><strong>{{ $item->total }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endif

@if (($showRatingComparison ?? false) && ! empty($ratingPercentagesBySede) && $ratingPercentagesBySede->isNotEmpty())
    @php $percentageOptions = ['atencion' => 'Atención a la Mesa', 'comida' => 'Calidad de la Comida', 'tiempo' => 'Tiempo de Entrega', 'ambientacion' => 'Ambientación']; @endphp
    <section class="section">
        <p class="section-eyebrow">Indicadores de cumplimiento</p>
        <h2 class="section-title">Resultados del periodo <small>Porcentajes por sede</small></h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sede</th>
                    @foreach ($percentageOptions as $label)
                        <th class="text-center">{{ $label }}</th>
                    @endforeach
                    <th class="text-center">Total general</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ratingPercentagesBySede as $item)
                    <tr class="{{ $item->sede === 'TOTAL GENERAL' ? 'total-row' : '' }}">
                        <td><strong>{{ $item->sede }}</strong></td>
                        @foreach ($percentageOptions as $key => $label)
                            @php $isLow = $key === 'tiempo' ? $item->$key < 96 : $item->$key < 98; @endphp
                            <td class="text-center" style="{{ $isLow ? 'color:#b7483e; font-weight:bold;' : '' }}">{{ number_format($item->$key, 2) }}%</td>
                        @endforeach
                        @php $averageIsLow = $item->promedio < 98; @endphp
                        <td class="text-center" style="{{ $averageIsLow ? 'color:#b7483e;' : '' }}"><strong>{{ number_format($item->promedio, 2) }}%</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endif

@if ($optionsBreakdown->isNotEmpty())
    <section class="section">
        <p class="section-eyebrow">Tipo de solicitud</p>
        <h2 class="section-title">Distribución por opción</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Opción</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Porcentaje</th>
                    <th style="width: 42%;">Participación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($optionsBreakdown as $item)
                    <tr>
                        <td><strong>{{ $item->opcion }}</strong></td>
                        <td class="text-center">{{ $item->total }}</td>
                        <td class="text-center">{{ number_format($item->porcentaje, 1) }}%</td>
                        <td>
                            <div class="bar-track">
                                <div class="bar-fill {{ $optionClasses[$item->opcion] ?? 'bar-sugerencia' }}" style="width: {{ min(100, max(0, (float) $item->porcentaje)) }}%;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endif

<section class="section">
    <p class="section-eyebrow">Tendencia</p>
    <h2 class="section-title">Actividad diaria</h2>
    @php
        $dailyTotal = $dailySubmissions->sum('total');
        $totalDays = $dailySubmissions->count();
        $activeDays = $dailySubmissions->where('total', '>', 0)->count();
        $peakDay = $dailySubmissions->sortByDesc('total')->first();
        $maxDaily = max((int) ($peakDay->total ?? 0), 1);
        $chartHeight = 68;
    @endphp

    @if ($dailyTotal > 0)
        <div class="summary-box">
            <strong>{{ $dailyTotal }} solicitudes</strong> en {{ $totalDays }} días, con actividad en {{ $activeDays }}.
            @if ($peakDay && $peakDay->total > 0)
                Día pico: <strong>{{ $peakDay->total }} el {{ $peakDay->label }}</strong>.
            @endif
            Rango: {{ $dailySubmissions->first()->label }} - {{ $dailySubmissions->last()->label }}.
        </div>

        @php
            $numDays = $dailySubmissions->count();
            $columnWidth = $numDays > 0 ? round(100 / $numDays, 2) : 100;
            $labelInterval = $numDays > 20 ? 3 : ($numDays > 10 ? 2 : 1);
            $barWidth = $numDays > 25 ? 4 : ($numDays > 15 ? 6 : ($numDays > 8 ? 10 : 14));
        @endphp
        <table class="daily-chart">
            <tr>
                <td class="daily-axis">{{ $maxDaily }}</td>
                @foreach ($dailySubmissions as $daily)
                    @php
                        $barHeight = max($daily->total > 0 ? ($daily->total / $maxDaily) * $chartHeight : 2, $daily->total > 0 ? 4 : 2);
                        $isPeak = $peakDay && $daily->date === $peakDay->date && $daily->total > 0;
                    @endphp
                    <td style="width: {{ $columnWidth }}%;">
                        <div class="daily-count">{{ $daily->total ?: '' }}</div>
                        <div class="daily-bar-cell">
                            <span class="daily-bar {{ $daily->total > 0 ? ($isPeak ? 'peak' : 'fill') : 'empty' }}" style="height: {{ $barHeight }}px; width: {{ $barWidth }}px;"></span>
                        </div>
                        <div class="daily-label">{{ $loop->iteration % $labelInterval === 0 || $loop->last ? $daily->label : '' }}</div>
                    </td>
                @endforeach
            </tr>
        </table>

        @if ($numDays > 7)
            @php
                $weekData = collect();
                $currentWeek = [];
                $weekStart = null;
                foreach ($dailySubmissions as $daily) {
                    $dayOfWeek = (int) date('w', strtotime($daily->date));
                    if ($dayOfWeek === 0 || empty($currentWeek)) {
                        if (! empty($currentWeek)) {
                            $weekData->push((object) [
                                'label' => $weekStart.' - '.date('d/m', strtotime($daily->date.' -1 day')),
                                'total' => array_sum($currentWeek),
                            ]);
                        }
                        $currentWeek = [];
                        $weekStart = $daily->label;
                    }
                    $currentWeek[] = $daily->total;
                }
                if (! empty($currentWeek)) {
                    $weekData->push((object) [
                        'label' => $weekStart.' - '.$dailySubmissions->last()->label,
                        'total' => array_sum($currentWeek),
                    ]);
                }
            @endphp
            <table class="data-table" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Semana</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Porcentaje</th>
                        <th style="width: 42%;">Participación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($weekData as $week)
                        @php $weekPercentage = $dailyTotal > 0 ? round($week->total / $dailyTotal * 100, 1) : 0; @endphp
                        <tr>
                            <td>{{ $week->label }}</td>
                            <td class="text-center">{{ $week->total }}</td>
                            <td class="text-center">{{ number_format($weekPercentage, 1) }}%</td>
                            <td>
                                <div class="bar-track"><div class="bar-fill" style="width: {{ $weekPercentage }}%; background:#527ea1;"></div></div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @else
        <div class="summary-box text-center">No hay PQRSF registradas en el rango de tendencia.</div>
    @endif
</section>

<div class="footer">
    Reporte generado el {{ $generatedAt }} - Sistema PQRSF - {{ $stats['total'] }} solicitudes procesadas
</div>
</body>
</html>
