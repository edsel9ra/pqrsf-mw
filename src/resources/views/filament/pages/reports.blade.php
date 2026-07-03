<x-filament-panels::page>
    <style>
        .pqrs-report-page {
            --pqrs-ink: #1f2937;
            --pqrs-muted: #6b7280;
            --pqrs-line: rgba(148, 163, 184, 0.24);
            --pqrs-card: rgba(255, 255, 255, 0.96);
            --pqrs-amber: #d97706;
            --pqrs-blue: #2563eb;
            --pqrs-green: #16a34a;
            --pqrs-red: #dc2626;
            display: grid;
            gap: 1.25rem;
            width: 100%;
            min-width: 0;
            max-width: 100%;
            overflow-x: hidden;
        }

        .pqrs-report-page *,
        .pqrs-report-page *::before,
        .pqrs-report-page *::after {
            box-sizing: border-box;
        }

        .pqrs-report-page :is(.fi-fo-component-ctn, .fi-fo-field-wrp, .fi-input-wrp, .fi-select-input, .fi-input) {
            min-width: 0;
            max-width: 100%;
        }

        .pqrs-report-shell,
        .pqrs-report-card,
        .pqrs-report-table-card,
        .pqrs-report-empty {
            background: var(--pqrs-card);
            border: 1px solid var(--pqrs-line);
            border-radius: 1.1rem;
            box-sizing: border-box;
            width: 100%;
            min-width: 0;
            max-width: 100%;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.05);
        }

        .pqrs-report-shell {
            padding: clamp(0.85rem, 2.5vw, 1.1rem);
        }

        .pqrs-report-filter-head,
        .pqrs-report-toolbar,
        .pqrs-report-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-width: 0;
            max-width: 100%;
        }

        .pqrs-report-filter-head > *,
        .pqrs-report-toolbar > *,
        .pqrs-report-section-head > * {
            min-width: 0;
        }

        .pqrs-report-filter-head {
            margin-bottom: 1rem;
        }

        .pqrs-report-eyebrow {
            margin: 0 0 0.2rem;
            color: var(--pqrs-amber);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .pqrs-report-title {
            margin: 0;
            color: var(--pqrs-ink);
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .pqrs-report-subtitle,
        .pqrs-report-generated,
        .pqrs-report-filters {
            color: var(--pqrs-muted);
            font-size: 0.86rem;
            overflow-wrap: anywhere;
        }

        .pqrs-report-form {
            padding-top: 0.1rem;
        }

        .pqrs-report-result {
            display: grid;
            gap: 1.25rem;
            width: 100%;
            min-width: 0;
            max-width: 100%;
        }

        .pqrs-report-toolbar {
            flex-wrap: wrap;
            padding: clamp(0.85rem, 2.5vw, 1rem);
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.08), rgba(37, 99, 235, 0.06));
            border: 1px solid rgba(217, 119, 6, 0.18);
            border-radius: 1rem;
        }

        .pqrs-report-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            min-width: 0;
            max-width: 100%;
        }

        .pqrs-report-actions > * {
            flex: 1 1 10rem;
        }

        .pqrs-report-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(12rem, 100%), 1fr));
            gap: 0.9rem;
            min-width: 0;
            max-width: 100%;
        }

        .pqrs-report-stat {
            position: relative;
            min-height: 7.2rem;
            overflow: hidden;
            padding: 1rem;
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border: 1px solid var(--pqrs-line);
            border-radius: 1rem;
            min-width: 0;
        }

        .pqrs-report-stat::after {
            content: '';
            position: absolute;
            right: -1.2rem;
            bottom: -1.7rem;
            width: 5.5rem;
            height: 5.5rem;
            background: currentColor;
            border-radius: 999px;
            opacity: 0.08;
        }

        .pqrs-report-stat.total { color: #334155; }
        .pqrs-report-stat.pending { color: var(--pqrs-amber); }
        .pqrs-report-stat.validated { color: var(--pqrs-blue); }
        .pqrs-report-stat.sent { color: var(--pqrs-green); }

        .pqrs-report-stat-label {
            display: block;
            color: var(--pqrs-muted);
            font-size: 0.8rem;
            font-weight: 700;
        }

        .pqrs-report-stat-value {
            display: block;
            margin-top: 0.45rem;
            color: currentColor;
            font-size: clamp(1.85rem, 4vw, 2.65rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.05em;
        }

        .pqrs-report-stat-note {
            display: block;
            margin-top: 0.7rem;
            color: var(--pqrs-muted);
            font-size: 0.78rem;
        }

        .pqrs-report-card {
            padding: clamp(0.9rem, 2.5vw, 1.1rem);
        }

        .pqrs-report-rating-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(8.75rem, 100%), 1fr));
            gap: 0.75rem;
            margin-top: 0.95rem;
            min-width: 0;
        }

        .pqrs-report-rating {
            padding: 0.9rem;
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 0.9rem;
            min-width: 0;
            text-align: center;
        }

        .pqrs-report-rating strong {
            display: block;
            color: var(--pqrs-ink);
            font-size: 1.45rem;
            line-height: 1;
            overflow-wrap: anywhere;
        }

        .pqrs-report-rating span {
            display: block;
            margin-top: 0.4rem;
            color: var(--pqrs-muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .pqrs-report-rating.general {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.12), rgba(255, 255, 255, 0.95));
            border-color: rgba(217, 119, 6, 0.24);
        }

        .pqrs-report-rating.good strong { color: var(--pqrs-green); }
        .pqrs-report-rating.mid strong { color: var(--pqrs-amber); }
        .pqrs-report-rating.low strong { color: var(--pqrs-red); }

        .pqrs-report-table-card {
            contain: inline-size;
            overflow: hidden;
        }

        .pqrs-report-table-card .pqrs-report-section-head {
            padding: 1rem 1.1rem 0.8rem;
            border-bottom: 1px solid var(--pqrs-line);
        }

        .pqrs-report-table-wrap {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .pqrs-report-table {
            width: 100%;
            min-width: 42rem;
            border-collapse: collapse;
            font-size: 0.88rem;
        }

        .pqrs-report-table th,
        .pqrs-report-table td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
        }

        .pqrs-report-table th {
            color: var(--pqrs-muted);
            background: #f8fafc;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .pqrs-report-table td {
            color: var(--pqrs-ink);
        }

        .pqrs-report-table th:not(:first-child),
        .pqrs-report-table td:not(:first-child) {
            text-align: center;
        }

        .pqrs-report-badge {
            display: inline-flex;
            min-width: 3.15rem;
            justify-content: center;
            padding: 0.22rem 0.5rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .pqrs-report-badge.good { background: rgba(22, 163, 74, 0.12); color: #15803d; }
        .pqrs-report-badge.mid { background: rgba(217, 119, 6, 0.13); color: #b45309; }
        .pqrs-report-badge.low { background: rgba(220, 38, 38, 0.11); color: #b91c1c; }

        .pqrs-report-split {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(20rem, 100%), 1fr));
            gap: 1rem;
            min-width: 0;
            max-width: 100%;
        }

        .pqrs-report-bars {
            display: grid;
            gap: 0.85rem;
            margin-top: 1rem;
            min-width: 0;
        }

        .pqrs-report-bar-row {
            display: grid;
            gap: 0.35rem;
        }

        .pqrs-report-bar-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            color: var(--pqrs-ink);
            font-size: 0.84rem;
            font-weight: 700;
            min-width: 0;
        }

        .pqrs-report-bar-meta small {
            color: var(--pqrs-muted);
            font-size: 0.8rem;
            font-weight: 700;
        }

        .pqrs-report-bar-track {
            height: 0.58rem;
            overflow: hidden;
            background: #e5e7eb;
            border-radius: 999px;
            min-width: 0;
        }

        .pqrs-report-bar-fill {
            height: 100%;
            min-width: 0;
            border-radius: inherit;
        }

        .pqrs-report-bar-fill.queja { background: var(--pqrs-red); }
        .pqrs-report-bar-fill.reclamo { background: var(--pqrs-amber); }
        .pqrs-report-bar-fill.peticion { background: var(--pqrs-blue); }
        .pqrs-report-bar-fill.sugerencia { background: #64748b; }
        .pqrs-report-bar-fill.felicitacion { background: var(--pqrs-green); }
        .pqrs-report-bar-fill.pending { background: var(--pqrs-amber); }
        .pqrs-report-bar-fill.validated { background: var(--pqrs-blue); }
        .pqrs-report-bar-fill.sent { background: var(--pqrs-green); }

        .pqrs-report-footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            width: 100%;
            min-width: 0;
            max-width: 100%;
            color: var(--pqrs-muted);
            font-size: 0.82rem;
            overflow-wrap: anywhere;
        }

        @media (max-width: 1100px) {
            .pqrs-report-split {
                grid-template-columns: 1fr;
            }

            .pqrs-report-table {
                min-width: 36rem;
            }
        }

        .pqrs-report-empty {
            padding: 2.25rem 1.5rem;
            color: var(--pqrs-muted);
            text-align: center;
        }

        .pqrs-report-inline-empty {
            margin-top: 1rem;
            padding: 1rem;
            color: var(--pqrs-muted);
            background: #f8fafc;
            border: 1px dashed rgba(148, 163, 184, 0.45);
            border-radius: 0.9rem;
            text-align: center;
        }

        @media (max-width: 900px) {
            .pqrs-report-rating.general {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 640px) {
            .pqrs-report-page {
                gap: 1rem;
            }

            .pqrs-report-filter-head,
            .pqrs-report-toolbar,
            .pqrs-report-section-head,
            .pqrs-report-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .pqrs-report-toolbar {
                border-radius: 0.9rem;
            }

            .pqrs-report-title {
                font-size: 1rem;
            }

            .pqrs-report-stat {
                min-height: 6.4rem;
            }

            .pqrs-report-actions {
                display: grid;
                grid-template-columns: 1fr;
                width: 100%;
            }

            .pqrs-report-actions > *,
            .pqrs-report-actions .fi-btn {
                width: 100%;
                justify-content: center;
            }

            .pqrs-report-rating-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pqrs-report-rating.general {
                grid-column: 1 / -1;
            }

            .pqrs-report-table-card .pqrs-report-section-head {
                padding: 0.95rem;
            }

            .pqrs-report-table-wrap {
                overflow-x: visible;
            }

            .pqrs-report-table,
            .pqrs-report-table tbody,
            .pqrs-report-table tr,
            .pqrs-report-table td {
                display: block;
                width: 100%;
            }

            .pqrs-report-table {
                min-width: 0;
            }

            .pqrs-report-table thead {
                display: none;
            }

            .pqrs-report-table tbody {
                display: grid;
                gap: 0.8rem;
                padding: 0.9rem;
            }

            .pqrs-report-table tr {
                padding: 0.85rem;
                background: #f8fafc;
                border: 1px solid rgba(148, 163, 184, 0.22);
                border-radius: 0.95rem;
            }

            .pqrs-report-table td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.48rem 0;
                border-bottom: 1px dashed rgba(148, 163, 184, 0.25);
                text-align: right !important;
            }

            .pqrs-report-table td:last-child {
                border-bottom: 0;
            }

            .pqrs-report-table td::before {
                content: attr(data-label);
                color: var(--pqrs-muted);
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }

            .pqrs-report-table td:first-child {
                display: block;
                padding-top: 0;
                text-align: left !important;
            }

            .pqrs-report-table td:first-child::before {
                display: block;
                margin-bottom: 0.25rem;
            }

        }

        @media (max-width: 420px) {
            .pqrs-report-rating-grid {
                grid-template-columns: 1fr;
            }

            .pqrs-report-stat-value {
                font-size: 2rem;
            }

            .pqrs-report-bar-meta {
                display: grid;
                gap: 0.2rem;
            }
        }
    </style>

    <div class="pqrs-report-page">
        <section class="pqrs-report-shell">
            <div class="pqrs-report-filter-head">
                <div>
                    <p class="pqrs-report-eyebrow">Panel de análisis</p>
                    <h2 class="pqrs-report-title">Filtros del reporte</h2>
                </div>
                <span class="pqrs-report-subtitle">Seleccione criterios y use Generar Reporte.</span>
            </div>

            <div class="pqrs-report-form">
                {{ $this->form }}
            </div>
        </section>

        @if ($showReport && count($reportData) > 0)
            @php
                $stats = $reportData['stats'];
                $ratingClass = fn ($value) => $value >= 4 ? 'good' : ($value >= 3 ? 'mid' : 'low');
                $optionClass = [
                    'Queja' => 'queja',
                    'Reclamo' => 'reclamo',
                    'Petición' => 'peticion',
                    'Sugerencia' => 'sugerencia',
                    'Felicitación' => 'felicitacion',
                ];
            @endphp

            <div class="pqrs-report-result">
                <div class="pqrs-report-toolbar">
                    <div>
                        <p class="pqrs-report-eyebrow">Resultado generado</p>
                        <h2 class="pqrs-report-title">Resumen PQRSF</h2>
                    </div>
                    <div class="pqrs-report-actions">
                        <x-filament::button tag="a" href="{{ $this->getDownloadUrl('pdf') }}" icon="heroicon-o-document-arrow-down" color="danger" target="_blank">
                            Descargar PDF
                        </x-filament::button>
                    </div>
                </div>

                <div class="pqrs-report-stats-grid">
                    <article class="pqrs-report-stat total">
                        <span class="pqrs-report-stat-label">Total PQRSF</span>
                        <strong class="pqrs-report-stat-value">{{ $stats['total'] }}</strong>
                        <span class="pqrs-report-stat-note">Solicitudes del periodo</span>
                    </article>
                    <article class="pqrs-report-stat pending">
                        <span class="pqrs-report-stat-label">Pendientes</span>
                        <strong class="pqrs-report-stat-value">{{ $stats['pending'] }}</strong>
                        <span class="pqrs-report-stat-note">Por validar</span>
                    </article>
                    <article class="pqrs-report-stat validated">
                        <span class="pqrs-report-stat-label">Validados</span>
                        <strong class="pqrs-report-stat-value">{{ $stats['validated'] }}</strong>
                        <span class="pqrs-report-stat-note">Listos para enviar</span>
                    </article>
                    <article class="pqrs-report-stat sent">
                        <span class="pqrs-report-stat-label">Enviados</span>
                        <strong class="pqrs-report-stat-value">{{ $stats['sent'] }}</strong>
                        <span class="pqrs-report-stat-note">Notificados a sede</span>
                    </article>
                </div>

                <section class="pqrs-report-card">
                    <div class="pqrs-report-section-head">
                        <div>
                            <p class="pqrs-report-eyebrow">Calidad percibida</p>
                            <h3 class="pqrs-report-title">Promedio de calificaciones</h3>
                        </div>
                        <span class="pqrs-report-subtitle">Escala 1 a 5</span>
                    </div>
                    <div class="pqrs-report-rating-grid">
                        <div class="pqrs-report-rating general {{ $ratingClass($stats['avg_general']) }}">
                            <strong>{{ number_format($stats['avg_general'], 1) }}/5</strong>
                            <span>General</span>
                        </div>
                        <div class="pqrs-report-rating {{ $ratingClass($stats['avg_ambientacion']) }}">
                            <strong>{{ number_format($stats['avg_ambientacion'], 1) }}</strong>
                            <span>Ambientación</span>
                        </div>
                        <div class="pqrs-report-rating {{ $ratingClass($stats['avg_atencion']) }}">
                            <strong>{{ number_format($stats['avg_atencion'], 1) }}</strong>
                            <span>Atención</span>
                        </div>
                        <div class="pqrs-report-rating {{ $ratingClass($stats['avg_comida']) }}">
                            <strong>{{ number_format($stats['avg_comida'], 1) }}</strong>
                            <span>Comida</span>
                        </div>
                        <div class="pqrs-report-rating {{ $ratingClass($stats['avg_tiempo']) }}">
                            <strong>{{ number_format($stats['avg_tiempo'], 1) }}</strong>
                            <span>Tiempo</span>
                        </div>
                    </div>
                </section>

                @if ($reportData['ratingsBySede']->isNotEmpty())
                    <section class="pqrs-report-table-card">
                        <div class="pqrs-report-section-head">
                            <div>
                                <p class="pqrs-report-eyebrow">Comparativo</p>
                                <h3 class="pqrs-report-title">Calificaciones por sede</h3>
                            </div>
                        </div>
                        <div class="pqrs-report-table-wrap">
                            <table class="pqrs-report-table">
                                <thead>
                                    <tr>
                                        <th>Sede</th>
                                        <th>Amb.</th>
                                        <th>Aten.</th>
                                        <th>Com.</th>
                                        <th>Tiem.</th>
                                        <th>Prom.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData['ratingsBySede'] as $sede)
                                        <tr>
                                            <td data-label="Sede"><strong>{{ $sede->sede_nombre }}</strong></td>
                                            @foreach (['ambientacion' => 'Amb.', 'atencion' => 'Aten.', 'comida' => 'Com.', 'tiempo' => 'Tiem.'] as $field => $label)
                                                <td data-label="{{ $label }}"><span class="pqrs-report-badge {{ $ratingClass($sede->$field) }}">{{ number_format($sede->$field, 1) }}</span></td>
                                            @endforeach
                                            <td data-label="Prom."><span class="pqrs-report-badge {{ $ratingClass($sede->promedio) }}">{{ number_format($sede->promedio, 1) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($reportData['pqrsfBySede']->isNotEmpty())
                    @php
                        $bySedeOptions = ['Felicitación', 'Queja', 'Reclamo', 'Sugerencia', 'Petición'];
                    @endphp
                    <section class="pqrs-report-table-card">
                        <div class="pqrs-report-section-head">
                            <div>
                                <p class="pqrs-report-eyebrow">Cantidad por sede</p>
                                <h3 class="pqrs-report-title">PQRSF por sedes</h3>
                            </div>
                        </div>
                        <div class="pqrs-report-table-wrap">
                            <table class="pqrs-report-table">
                                <thead>
                                    <tr>
                                        <th>Sede</th>
                                        @foreach ($bySedeOptions as $opt)
                                            <th>{{ strtoupper($opt) }}</th>
                                        @endforeach
                                        <th>Total General</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData['pqrsfBySede'] as $item)
                                        <tr>
                                            <td data-label="Sede"><strong>{{ $item->sede }}</strong></td>
                                            @foreach ($bySedeOptions as $opt)
                                                <td data-label="{{ strtoupper($opt) }}">{{ $item->$opt }}</td>
                                            @endforeach
                                            <td data-label="Total General"><strong>{{ $item->total }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if (! empty($reportData['ratingPercentagesBySede']) && $reportData['ratingPercentagesBySede']->isNotEmpty())
                    @php
                        $pctOptions = ['atencion' => 'Atención a la Mesa', 'comida' => 'Calidad de la Comida', 'tiempo' => 'Tiempo de Entrega', 'ambientacion' => 'Ambientación'];
                    @endphp
                    <section class="pqrs-report-table-card">
                        <div class="pqrs-report-section-head">
                            <div>
                                <p class="pqrs-report-eyebrow">Resultados por sede</p>
                                <h3 class="pqrs-report-title">Resultados del periodo (porcentajes)</h3>
                            </div>
                        </div>
                        <div class="pqrs-report-table-wrap">
                            <table class="pqrs-report-table">
                                <thead>
                                    <tr>
                                        <th>Sede</th>
                                        @foreach ($pctOptions as $key => $label)
                                            <th>{{ $label }}</th>
                                        @endforeach
                                        <th>Total General</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData['ratingPercentagesBySede'] as $item)
                                        <tr>
                                            <td data-label="Sede"><strong>{{ $item->sede }}</strong></td>
                                            @foreach ($pctOptions as $key => $label)
                                                @php $isLow = $item->$key < 98; @endphp
                                                <td data-label="{{ $label }}" style="{{ $isLow ? 'color:#dc2626; font-weight:700;' : '' }}">{{ number_format($item->$key, 2) }}%</td>
                                            @endforeach
                                            @php $promIsLow = $item->promedio < 98; @endphp
                                            <td data-label="Total General" style="{{ $promIsLow ? 'color:#dc2626; font-weight:700;' : '' }}"><strong>{{ number_format($item->promedio, 2) }}%</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($reportData['optionsBreakdown']->isNotEmpty())
                    <div class="pqrs-report-split">
                        <section class="pqrs-report-card">
                            <div>
                                <p class="pqrs-report-eyebrow">Tipo de solicitud</p>
                                <h3 class="pqrs-report-title">Distribución por opción</h3>
                            </div>
                            <div class="pqrs-report-bars">
                                @foreach ($reportData['optionsBreakdown'] as $item)
                                    <div class="pqrs-report-bar-row">
                                        <div class="pqrs-report-bar-meta">
                                            <span>{{ $item->opcion }}</span>
                                            <small>{{ $item->total }} ({{ $item->porcentaje }}%)</small>
                                        </div>
                                        <div class="pqrs-report-bar-track">
                                            <div class="pqrs-report-bar-fill {{ $optionClass[$item->opcion] ?? 'sugerencia' }}" style="width: {{ $item->porcentaje }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="pqrs-report-card">
                            <div>
                                <p class="pqrs-report-eyebrow">Flujo operativo</p>
                                <h3 class="pqrs-report-title">Distribución por estado</h3>
                            </div>
                            <div class="pqrs-report-bars">
                                @foreach ($reportData['statusDistribution'] as $item)
                                    <div class="pqrs-report-bar-row">
                                        <div class="pqrs-report-bar-meta">
                                            <span>{{ $item->label }}</span>
                                            <small>{{ $item->total }} ({{ $item->porcentaje }}%)</small>
                                        </div>
                                        <div class="pqrs-report-bar-track">
                                            <div class="pqrs-report-bar-fill {{ $item->status }}" style="width: {{ $item->porcentaje }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>
                @endif

                @if ($reportData['dailySubmissions']->isNotEmpty())
                    @php
                        $dailyTotal = $reportData['dailySubmissions']->sum('total');
                        $numDays = $reportData['dailySubmissions']->count();
                        $avgDaily = $numDays > 0 ? round($dailyTotal / $numDays, 1) : 0;
                        $maxDaily = $reportData['dailySubmissions']->max('total');
                        $peakDay = $reportData['dailySubmissions']->firstWhere('total', $maxDaily);
                        $daysWithActivity = $reportData['dailySubmissions']->filter(fn ($d) => $d->total > 0)->count();
                        $trendClass = $dailyTotal > 0 ? ($avgDaily >= 2 ? 'good' : ($avgDaily >= 1 ? 'mid' : 'low')) : 'low';
                    @endphp
                    <section class="pqrs-report-card">
                        <div class="pqrs-report-section-head">
                            <div>
                                <p class="pqrs-report-eyebrow">Tendencia</p>
                                <h3 class="pqrs-report-title">Resumen de actividad diaria</h3>
                            </div>
                        </div>
                        @if ($dailyTotal > 0)
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(10rem, 1fr)); gap:0.75rem; margin-top:0.75rem;">
                                <div style="text-align:center; padding:0.9rem; background:#f8fafc; border:1px solid var(--pqrs-line); border-radius:0.9rem;">
                                    <strong style="display:block; color:var(--pqrs-ink); font-size:1.5rem; font-weight:900; letter-spacing:-0.03em;">{{ $dailyTotal }}</strong>
                                    <span style="color:var(--pqrs-muted); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;">Total del periodo</span>
                                </div>
                                <div style="text-align:center; padding:0.9rem; background:#f8fafc; border:1px solid var(--pqrs-line); border-radius:0.9rem;">
                                    <strong style="display:block; color:var(--pqrs-ink); font-size:1.5rem; font-weight:900; letter-spacing:-0.03em;">{{ number_format($avgDaily, 1) }}</strong>
                                    <span style="color:var(--pqrs-muted); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;">Promedio diario</span>
                                </div>
                                <div style="text-align:center; padding:0.9rem; background:#f8fafc; border:1px solid var(--pqrs-line); border-radius:0.9rem;">
                                    <strong style="display:block; color:var(--pqrs-blue); font-size:1.5rem; font-weight:900; letter-spacing:-0.03em;">{{ $maxDaily }}</strong>
                                    <span style="color:var(--pqrs-muted); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;">Día pico</span>
                                    @if ($peakDay)
                                        <span style="display:block; color:var(--pqrs-muted); font-size:0.7rem; margin-top:0.2rem;">{{ $peakDay->label }}</span>
                                    @endif
                                </div>
                                <div style="text-align:center; padding:0.9rem; background:#f8fafc; border:1px solid var(--pqrs-line); border-radius:0.9rem;">
                                    <strong style="display:block; color:var(--pqrs-green); font-size:1.5rem; font-weight:900; letter-spacing:-0.03em;">{{ $daysWithActivity }}/{{ $numDays }}</strong>
                                    <span style="color:var(--pqrs-muted); font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;">Días con actividad</span>
                                </div>
                            </div>
                        @else
                            <div class="pqrs-report-inline-empty">No hay PQRSF registradas en el rango de tendencia.</div>
                        @endif
                    </section>
                @endif

                <footer class="pqrs-report-footer">
                    <span class="pqrs-report-filters">
                        @if (! empty($reportData['filterLabels']))
                            Filtros: {{ implode(' | ', $reportData['filterLabels']) }}
                        @else
                            Filtros: Todos los registros disponibles
                        @endif
                    </span>
                    <span class="pqrs-report-generated">Generado: {{ $reportData['generatedAt'] }}</span>
                </footer>
            </div>
        @elseif ($showReport)
            <section class="pqrs-report-empty">
                <p class="pqrs-report-eyebrow">Sin resultados</p>
                <h2 class="pqrs-report-title">No se encontraron datos con los filtros seleccionados.</h2>
            </section>
        @endif
    </div>
</x-filament-panels::page>
