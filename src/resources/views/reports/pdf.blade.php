<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte PQRSF</title>
    <style>
        @page {
            margin: 18px 20px 18px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.4;
        }

        .header {
            background: #dc2626;
            padding: 10px 16px 9px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            vertical-align: middle;
        }
        .header-logo {
            width: 86px;
            text-align: right;
            vertical-align: middle;
        }
        .header-logo img {
            width: 72px;
            height: auto;
            background: #fff;
            border-radius: 4px;
            padding: 5px;
        }
        .header h1 {
            font-size: 18px;
            color: #fff;
            margin: 0;
            letter-spacing: -0.01em;
            font-weight: 800;
        }
        .header-meta {
            font-size: 7px;
            color: rgba(255,255,255,0.82);
            margin-top: 2px;
        }
        .header-meta .tag {
            background: rgba(255,255,255,0.15);
            padding: 1px 6px;
            border-radius: 3px;
            margin-left: 5px;
            font-size: 6.5px;
        }

        h2 {
            font-size: 11px;
            color: #1e293b;
            margin: 8px 0 4px;
            padding: 0 0 3px;
            border-bottom: 2px solid #dc2626;
            font-weight: 700;
        }
        h2.compact {
            margin: 4px 0 2px;
        }

        .grid {
            width: 100%;
            display: table;
            border-collapse: separate;
            border-spacing: 4px;
            margin: 4px 0 6px;
        }
        .grid-row {
            display: table-row;
        }
        .grid-cell {
            display: table-cell;
            padding: 4px 5px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            text-align: center;
            background: #fff;
            vertical-align: middle;
        }
        .grid-cell .num {
            font-size: 14px;
            font-weight: 800;
            line-height: 1.2;
        }
        .grid-cell .lbl {
            font-size: 6px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-top: 1px;
        }
        .grid-cell.stat-total { border-left: 3px solid #64748b; }
        .grid-cell.stat-total .num { color: #475569; }
        .grid-cell.stat-pending { border-left: 3px solid #f59e0b; }
        .grid-cell.stat-pending .num { color: #d97706; }
        .grid-cell.stat-validated { border-left: 3px solid #3b82f6; }
        .grid-cell.stat-validated .num { color: #2563eb; }
        .grid-cell.stat-sent { border-left: 3px solid #22c55e; }
        .grid-cell.stat-sent .num { color: #16a34a; }
        .grid-cell.rating-cell {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .grid-cell.rating-cell .num { font-size: 15px; }

        .val-green { color: #16a34a; }
        .val-amber { color: #d97706; }
        .val-red { color: #dc2626; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0 5px;
            font-size: 7.5px;
        }
        thead th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            padding: 3px 5px;
            text-align: left;
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #cbd5e1;
        }
        tbody td {
            padding: 2px 5px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .badge {
            display: inline-block;
            padding: 0 5px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: 700;
        }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }

        .bar-container {
            width: 100%;
            background: #e2e8f0;
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
        }
        .bar-fill {
            height: 10px;
            border-radius: 5px;
        }

        .daily-grid-chart {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0 0;
        }
        .daily-grid-chart td {
            padding: 0;
            vertical-align: bottom;
            text-align: center;
        }
        .daily-grid-chart .bar {
            display: block;
            margin: 0 auto;
            min-height: 2px;
            border-radius: 2px 2px 0 0;
        }
        .daily-grid-chart .bar.fill { background: #2563eb; }
        .daily-grid-chart .bar.empty { background: #e2e8f0; }
        .daily-grid-chart .bar.peak { background: #dc2626; }
        .daily-grid-chart .count {
            font-size: 5.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0;
        }
        .daily-grid-chart .xlabel {
            font-size: 5px;
            color: #64748b;
            padding-top: 1px;
            white-space: nowrap;
        }

        .daily-axis {
            font-size: 6px;
            color: #94a3b8;
            text-align: right;
            padding-right: 3px;
            vertical-align: bottom;
            width: 18px;
        }

        .footer {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #cbd5e1;
            font-size: 6.5px;
            color: #94a3b8;
            text-align: center;
        }

        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 8px;
            margin: 3px 0 4px;
            font-size: 7px;
            color: #475569;
        }
        .summary-box strong {
            color: #1e293b;
        }

        .layout-2col {
            width: 100%;
            display: table;
            border-collapse: separate;
            border-spacing: 10px;
            margin: 0;
        }
        .layout-2col .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
    </style>
</head>
<body>

<script type="text/php">
    if (isset($pdf)) {
        $font = Font_Metrics::get_font("DejaVu Sans", "normal");
        $pdf->page_text(760, 570, "{PAGE_NUM}", $font, 7, array(0.58, 0.58, 0.58), 0, 0);
    }
</script>

<div class="header">
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h1>Reporte PQRSF</h1>
                <div class="header-meta">
                    Generado: {{ $generatedAt }}
                    @foreach ($filterLabels as $label)
                        <span class="tag">{{ $label }}</span>
                    @endforeach
                </div>
            </td>
            @if ($logoSrc)
            <td class="header-logo">
                <img src="{{ $logoSrc }}" alt="Logo MW">
            </td>
            @endif
        </tr>
    </table>
</div>

<h2>Resumen general y calificaciones</h2>
<div class="grid">
    <div class="grid-row">
        <div class="grid-cell stat-total">
            <div class="num">{{ $stats['total'] }}</div>
            <div class="lbl">Total PQRSF</div>
        </div>
        <div class="grid-cell stat-pending">
            <div class="num">{{ $stats['pending'] }}</div>
            <div class="lbl">Pendientes</div>
        </div>
        <div class="grid-cell stat-validated">
            <div class="num">{{ $stats['validated'] }}</div>
            <div class="lbl">Validados</div>
        </div>
        <div class="grid-cell stat-sent">
            <div class="num">{{ $stats['sent'] }}</div>
            <div class="lbl">Enviados</div>
        </div>
        @php $rColor = fn($v) => $v >= 4 ? 'val-green' : ($v >= 3 ? 'val-amber' : 'val-red'); @endphp
        <div class="grid-cell rating-cell">
            <div class="num {{ $rColor($stats['avg_general']) }}">{{ number_format($stats['avg_general'], 1) }}</div>
            <div class="lbl">★ General</div>
        </div>
    </div>
</div>

<div class="grid">
    <div class="grid-row">
        @foreach ([
            ['label' => 'Ambientación', 'val' => $stats['avg_ambientacion']],
            ['label' => 'Atención', 'val' => $stats['avg_atencion']],
            ['label' => 'Comida', 'val' => $stats['avg_comida']],
            ['label' => 'Tiempo', 'val' => $stats['avg_tiempo']],
        ] as $rating)
            <div class="grid-cell">
                <div class="num {{ $rColor($rating['val']) }}">{{ number_format($rating['val'], 1) }}</div>
                <div class="lbl">{{ $rating['label'] }}</div>
            </div>
        @endforeach
    </div>
</div>

<h2>Calificaciones por sede</h2>
<table>
    <thead>
        <tr>
            <th>Sede</th>
            <th class="text-center">Amb.</th>
            <th class="text-center">Aten.</th>
            <th class="text-center">Com.</th>
            <th class="text-center">Tiem.</th>
            <th class="text-center">Prom.</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($ratingsBySede as $sede)
            <tr>
                <td><strong>{{ $sede->sede_nombre }}</strong></td>
                <td class="text-center">
                    <span class="badge {{ $sede->ambientacion >= 4 ? 'badge-green' : ($sede->ambientacion >= 3 ? 'badge-amber' : 'badge-red') }}">
                        {{ number_format($sede->ambientacion, 1) }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge {{ $sede->atencion >= 4 ? 'badge-green' : ($sede->atencion >= 3 ? 'badge-amber' : 'badge-red') }}">
                        {{ number_format($sede->atencion, 1) }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge {{ $sede->comida >= 4 ? 'badge-green' : ($sede->comida >= 3 ? 'badge-amber' : 'badge-red') }}">
                        {{ number_format($sede->comida, 1) }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge {{ $sede->tiempo >= 4 ? 'badge-green' : ($sede->tiempo >= 3 ? 'badge-amber' : 'badge-red') }}">
                        {{ number_format($sede->tiempo, 1) }}
                    </span>
                </td>
                <td class="text-center">
                    <strong>{{ number_format($sede->promedio, 1) }}</strong>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center" style="color:#94a3b8; padding:10px 0;">Sin datos</td></tr>
        @endforelse
    </tbody>
</table>

@if (!empty($pqrsfBySede) && $pqrsfBySede->isNotEmpty())
    @php
        $bySedeOptions = ['Felicitación', 'Queja', 'Reclamo', 'Sugerencia', 'Petición'];
    @endphp
    <h2>PQRSF por Sedes</h2>
    <table>
        <thead>
            <tr>
                <th>Sede</th>
                @foreach ($bySedeOptions as $opt)
                    <th class="text-center">{{ strtoupper($opt) }}</th>
                @endforeach
                <th class="text-center">Total General</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pqrsfBySede as $item)
                <tr>
                    <td><strong>{{ $item->sede }}</strong></td>
                    @foreach ($bySedeOptions as $opt)
                        <td class="text-center">{{ $item->$opt }}</td>
                    @endforeach
                    <td class="text-center"><strong>{{ $item->total }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if (!empty($ratingPercentagesBySede) && $ratingPercentagesBySede->isNotEmpty())
    @php
        $pctOpts = ['atencion' => 'Atención a la Mesa', 'comida' => 'Calidad de la Comida', 'tiempo' => 'Tiempo de Entrega', 'ambientacion' => 'Ambientación'];
    @endphp
    <h2>Resultados por sede (porcentajes)</h2>
    <table>
        <thead>
            <tr>
                <th>Sede</th>
                @foreach ($pctOpts as $key => $label)
                    <th class="text-center">{{ $label }}</th>
                @endforeach
                <th class="text-center">Total General</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ratingPercentagesBySede as $item)
                <tr>
                    <td><strong>{{ $item->sede }}</strong></td>
                    @foreach ($pctOpts as $key => $label)
                        @php $isLow = $key === 'tiempo' ? $item->$key < 96 : $item->$key < 98; @endphp
                        <td class="text-center" style="{{ $isLow ? 'color:#dc2626; font-weight:700;' : '' }}">{{ number_format($item->$key, 2) }}%</td>
                    @endforeach
                    @php $promIsLow = $item->promedio < 98; @endphp
                    <td class="text-center" style="{{ $promIsLow ? 'color:#dc2626; font-weight:700;' : '' }}"><strong>{{ number_format($item->promedio, 2) }}%</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<table class="layout-2col">
    <tr>
        <td class="col">
            <h2>Distribución por Opción</h2>
            <table>
                <thead>
                    <tr>
                        <th>Opción</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">%</th>
                        <th>Barra</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $barColors = [
                            'Queja' => '#ef4444',
                            'Reclamo' => '#f59e0b',
                            'Petición' => '#3b82f6',
                            'Sugerencia' => '#64748b',
                            'Felicitación' => '#22c55e',
                        ];
                    @endphp
                    @foreach ($optionsBreakdown as $item)
                        <tr>
                            <td>{{ $item->opcion }}</td>
                            <td class="text-center">{{ $item->total }}</td>
                            <td class="text-center">{{ $item->porcentaje }}%</td>
                            <td width="35%">
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: {{ $item->porcentaje }}%; background: {{ $barColors[$item->opcion] ?? '#94a3a8' }};"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </td>
        <td class="col">
            <h2>Distribución por Estado</h2>
            <table>
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">%</th>
                        <th>Barra</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statusDistribution as $item)
                        <tr>
                            <td>{{ $item->label }}</td>
                            <td class="text-center">{{ $item->total }}</td>
                            <td class="text-center">{{ $item->porcentaje }}%</td>
                            <td width="35%">
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: {{ $item->porcentaje }}%; background-color: {{ $item->color }}"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </td>
    </tr>
</table>

<h2>PQRSF por Día</h2>
@php
    $dailyTotal = $dailySubmissions->sum('total');
    $totalDays = $dailySubmissions->count();
    $activeDays = $dailySubmissions->where('total', '>', 0)->count();
    $peakDay = $dailySubmissions->sortByDesc('total')->first();
    $maxDaily = max($peakDay->total ?? 0, 1);
    $yMax = (int) ceil($maxDaily / 2) * 2;
    if ($yMax < 2) { $yMax = 2; }
    $chartHeight = 60;
@endphp

@if ($dailyTotal > 0)
    <div class="summary-box">
        <strong>Resumen diario:</strong>
        {{ $dailyTotal }} solicitudes en {{ $totalDays }} días
        @if ($activeDays > 0)
            (prom. {{ number_format($dailyTotal / $activeDays, 1) }}/día activo)
        @endif
        @if ($peakDay && $peakDay->total > 0)
            &mdash; <strong>Pico:</strong> {{ $peakDay->total }} el {{ $peakDay->label }}
        @endif
        &mdash; Rango: {{ $dailySubmissions->first()->label ?? '—' }} &ndash; {{ $dailySubmissions->last()->label ?? '—' }}
    </div>

    @php
        $numDays = $dailySubmissions->count();
        $colWidth = $numDays > 0 ? round(100 / $numDays, 2) : 100;
        $labelInterval = $numDays > 20 ? 3 : ($numDays > 10 ? 2 : 1);
        $barWidthPx = $numDays > 25 ? 4 : ($numDays > 15 ? 6 : ($numDays > 8 ? 10 : 14));
    @endphp

    <table class="daily-grid-chart">
        <tr>
            <td class="daily-axis">{{ $yMax }}</td>
            @foreach ($dailySubmissions as $daily)
                @php
                    $barH = max($daily->total > 0 ? ($daily->total / $yMax) * $chartHeight : 2, $daily->total > 0 ? 4 : 2);
                    $isPeak = $peakDay && $daily->date === $peakDay->date && $daily->total > 0;
                @endphp
                <td style="width: {{ $colWidth }}%;">
                    <div class="count">{{ $daily->total ?: '' }}</div>
                    <div class="bar-cell">
                        <div class="bar {{ $daily->total > 0 ? ($isPeak ? 'peak' : 'fill') : 'empty' }}" style="height: {{ $barH }}px; width: {{ $barWidthPx }}px;"></div>
                    </div>
                    <div class="xlabel">{{ $loop->iteration % $labelInterval === 0 || $loop->last ? $daily->label : '' }}</div>
                </td>
            @endforeach
        </tr>
        <tr>
            <td class="daily-axis">0</td>
            <td colspan="{{ $numDays }}" style="border-top: 1px solid #cbd5e1;"></td>
        </tr>
    </table>

    @if ($numDays > 7)
        @php
            $weekData = collect();
            $currentWeek = [];
            $weekStartStr = null;
            foreach ($dailySubmissions as $daily) {
                $dow = (int) date('w', strtotime($daily->date));
                if ($dow === 0 || empty($currentWeek)) {
                    if (!empty($currentWeek)) {
                        $endDate = date('d/m', strtotime($daily->date . ' -1 day'));
                        $weekData->push((object) [
                            'label' => $weekStartStr . ' - ' . $endDate,
                            'total' => array_sum($currentWeek),
                        ]);
                    }
                    $currentWeek = [];
                    $weekStartStr = $daily->label;
                }
                $currentWeek[] = $daily->total;
            }
            if (!empty($currentWeek)) {
                $weekData->push((object) [
                    'label' => $weekStartStr . ' - ' . $dailySubmissions->last()->label,
                    'total' => array_sum($currentWeek),
                ]);
            }
        @endphp
        <h2 class="compact">PQRSF por Semana</h2>
        <table class="compact-table">
            <thead>
                <tr>
                    <th>Semana</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">%</th>
                    <th>Barra</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($weekData as $week)
                    <tr>
                        <td>{{ $week->label }}</td>
                        <td class="text-center">{{ $week->total }}</td>
                        <td class="text-center">{{ $dailyTotal > 0 ? round($week->total / $dailyTotal * 100, 1) : 0 }}%</td>
                        <td width="45%">
                            <div class="bar-container">
                                <div class="bar-fill" style="width: {{ $dailyTotal > 0 ? round($week->total / $dailyTotal * 100, 1) : 0 }}%; background: #2563eb;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@else
    <div class="summary-box" style="text-align:center;">
        No hay PQRSF registradas en el rango de tendencia.
    </div>
@endif

<div class="footer">
    Reporte generado el {{ $generatedAt }} &mdash; Sistema PQRSF &mdash; {{ $stats['total'] }} solicitudes procesadas
</div>
</body>
</html>
