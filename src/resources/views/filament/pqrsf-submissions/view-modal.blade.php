@php
    $summary = $summary ?? [];
    $contactItems = $contactItems ?? [];
    $experienceItems = $experienceItems ?? [];
    $ratings = $ratings ?? [];
    $systemItems = $systemItems ?? [];
    $extraItems = $extraItems ?? [];
    $observations = $observations ?? '—';
@endphp

<div class="pqrs-submission-view">
    <style>
        .pqrs-submission-view {
            --pqrs-ink: #172033;
            --pqrs-muted: #667085;
            --pqrs-soft: #f8fafc;
            --pqrs-card: rgba(255, 255, 255, 0.96);
            --pqrs-line: rgba(148, 163, 184, 0.28);
            --pqrs-amber: #d97706;
            --pqrs-amber-soft: rgba(245, 158, 11, 0.14);
            --pqrs-blue: #2563eb;
            --pqrs-green: #16a34a;
            --pqrs-red: #dc2626;
            --pqrs-shadow: 0 22px 55px rgba(15, 23, 42, 0.08);
            display: grid;
            gap: 1rem;
            color: var(--pqrs-ink);
        }

        .dark .pqrs-submission-view {
            --pqrs-ink: #f8fafc;
            --pqrs-muted: #cbd5e1;
            --pqrs-soft: rgba(15, 23, 42, 0.92);
            --pqrs-card: rgba(15, 23, 42, 0.86);
            --pqrs-line: rgba(148, 163, 184, 0.24);
            --pqrs-shadow: 0 22px 55px rgba(0, 0, 0, 0.28);
        }

        .pqrs-submission-view *,
        .pqrs-submission-view *::before,
        .pqrs-submission-view *::after {
            box-sizing: border-box;
        }

        .pqrs-submission-hero {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            overflow: hidden;
            padding: clamp(1rem, 2.4vw, 1.35rem);
            background:
                radial-gradient(circle at 15% 20%, rgba(245, 158, 11, 0.28), transparent 28%),
                linear-gradient(135deg, #111827 0%, #1f2937 50%, #92400e 145%);
            border: 1px solid rgba(245, 158, 11, 0.24);
            border-radius: 1.25rem;
            box-shadow: var(--pqrs-shadow);
            color: #ffffff;
        }

        .pqrs-submission-hero::after {
            content: '';
            position: absolute;
            right: -5rem;
            top: -5rem;
            width: 13rem;
            height: 13rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
        }

        .pqrs-submission-eyebrow {
            margin: 0 0 0.35rem;
            color: #fbbf24;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .pqrs-submission-title {
            margin: 0;
            font-size: clamp(1.45rem, 4vw, 2.2rem);
            font-weight: 900;
            letter-spacing: -0.055em;
            line-height: 0.98;
        }

        .pqrs-submission-subtitle {
            margin: 0.55rem 0 0;
            max-width: 42rem;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.92rem;
            overflow-wrap: anywhere;
        }

        .pqrs-submission-badge-stack {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .pqrs-submission-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding: 0.38rem 0.7rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.11);
            color: #ffffff;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            text-align: center;
            white-space: nowrap;
        }

        .pqrs-submission-badge.status-pending { background: rgba(245, 158, 11, 0.22); border-color: rgba(251, 191, 36, 0.42); }
        .pqrs-submission-badge.status-validated { background: rgba(37, 99, 235, 0.24); border-color: rgba(96, 165, 250, 0.42); }
        .pqrs-submission-badge.status-sent { background: rgba(22, 163, 74, 0.24); border-color: rgba(74, 222, 128, 0.42); }
        .pqrs-submission-badge.option-complaint { background: rgba(220, 38, 38, 0.24); border-color: rgba(248, 113, 113, 0.45); }
        .pqrs-submission-badge.option-claim { background: rgba(217, 119, 6, 0.25); border-color: rgba(251, 191, 36, 0.46); }
        .pqrs-submission-badge.option-request { background: rgba(37, 99, 235, 0.24); border-color: rgba(96, 165, 250, 0.42); }
        .pqrs-submission-badge.option-suggestion { background: rgba(100, 116, 139, 0.28); border-color: rgba(203, 213, 225, 0.3); }
        .pqrs-submission-badge.option-praise { background: rgba(22, 163, 74, 0.24); border-color: rgba(74, 222, 128, 0.42); }

        .pqrs-submission-quick-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .pqrs-submission-quick-card,
        .pqrs-submission-panel,
        .pqrs-submission-observation,
        .pqrs-submission-system {
            background: var(--pqrs-card);
            border: 1px solid var(--pqrs-line);
            border-radius: 1.05rem;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.04);
        }

        .pqrs-submission-quick-card {
            position: relative;
            overflow: hidden;
            padding: 0.9rem;
        }

        .pqrs-submission-quick-card::after {
            content: '';
            position: absolute;
            right: -1.6rem;
            bottom: -1.8rem;
            width: 4.8rem;
            height: 4.8rem;
            background: currentColor;
            border-radius: 999px;
            opacity: 0.08;
        }

        .pqrs-submission-quick-card.sede { color: var(--pqrs-amber); }
        .pqrs-submission-quick-card.fecha { color: var(--pqrs-blue); }
        .pqrs-submission-quick-card.promedio.rating-good { color: var(--pqrs-green); }
        .pqrs-submission-quick-card.promedio.rating-mid { color: var(--pqrs-amber); }
        .pqrs-submission-quick-card.promedio.rating-low { color: var(--pqrs-red); }
        .pqrs-submission-quick-card.promedio.rating-empty { color: #64748b; }
        .pqrs-submission-quick-card.tipo { color: #475569; }

        .dark .pqrs-submission-quick-card.tipo { color: #cbd5e1; }

        .pqrs-submission-quick-label {
            display: block;
            color: var(--pqrs-muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .pqrs-submission-quick-value {
            position: relative;
            z-index: 1;
            display: block;
            margin-top: 0.45rem;
            color: var(--pqrs-ink);
            font-size: 1rem;
            font-weight: 850;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .pqrs-submission-quick-card.promedio .pqrs-submission-quick-value {
            color: currentColor;
            font-size: 1.8rem;
            letter-spacing: -0.05em;
        }

        .pqrs-submission-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
            gap: 1rem;
        }

        .pqrs-submission-panel,
        .pqrs-submission-observation,
        .pqrs-submission-system {
            padding: clamp(0.95rem, 2.2vw, 1.15rem);
        }

        .pqrs-submission-panel-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.8rem;
            margin-bottom: 0.9rem;
        }

        .pqrs-submission-panel-title {
            margin: 0;
            color: var(--pqrs-ink);
            font-size: 0.98rem;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .pqrs-submission-panel-kicker {
            color: var(--pqrs-amber);
            font-size: 0.7rem;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .pqrs-submission-data-list {
            display: grid;
            gap: 0.65rem;
            margin: 0;
        }

        .pqrs-submission-data-row {
            display: grid;
            grid-template-columns: minmax(7.5rem, 0.35fr) minmax(0, 1fr);
            gap: 0.75rem;
            align-items: start;
            padding: 0.72rem 0.78rem;
            background: var(--pqrs-soft);
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 0.82rem;
        }

        .pqrs-submission-data-row dt {
            color: var(--pqrs-muted);
            font-size: 0.78rem;
            font-weight: 800;
        }

        .pqrs-submission-data-row dd {
            margin: 0;
            color: var(--pqrs-ink);
            font-size: 0.9rem;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        .pqrs-submission-ratings {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .pqrs-submission-rating-card {
            position: relative;
            overflow: hidden;
            min-height: 9.2rem;
            padding: 0.9rem;
            background: linear-gradient(145deg, var(--pqrs-card), var(--pqrs-soft));
            border: 1px solid var(--pqrs-line);
            border-radius: 1rem;
        }

        .pqrs-submission-rating-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 0.28rem;
            background: currentColor;
            opacity: 0.85;
        }

        .pqrs-submission-rating-card.rating-good { color: var(--pqrs-green); }
        .pqrs-submission-rating-card.rating-mid { color: var(--pqrs-amber); }
        .pqrs-submission-rating-card.rating-low { color: var(--pqrs-red); }
        .pqrs-submission-rating-card.rating-empty { color: #64748b; }

        .pqrs-submission-rating-label {
            display: block;
            color: var(--pqrs-muted);
            font-size: 0.76rem;
            font-weight: 850;
        }

        .pqrs-submission-rating-score {
            display: block;
            margin-top: 0.65rem;
            color: currentColor;
            font-size: 2rem;
            font-weight: 950;
            line-height: 1;
            letter-spacing: -0.06em;
        }

        .pqrs-submission-rating-description {
            display: block;
            margin-top: 0.35rem;
            color: var(--pqrs-ink);
            font-size: 0.82rem;
            font-weight: 750;
        }

        .pqrs-submission-meter {
            height: 0.44rem;
            margin-top: 0.85rem;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.22);
            border-radius: 999px;
        }

        .pqrs-submission-meter span {
            display: block;
            height: 100%;
            background: currentColor;
            border-radius: inherit;
        }

        .pqrs-submission-observation {
            display: grid;
            gap: 0.8rem;
            background:
                linear-gradient(135deg, var(--pqrs-amber-soft), transparent 42%),
                var(--pqrs-card);
        }

        .pqrs-submission-observation-text {
            margin: 0;
            color: var(--pqrs-ink);
            font-size: 0.96rem;
            line-height: 1.65;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .pqrs-submission-system .pqrs-submission-data-list,
        .pqrs-submission-extra .pqrs-submission-data-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pqrs-submission-system .pqrs-submission-data-row,
        .pqrs-submission-extra .pqrs-submission-data-row {
            grid-template-columns: 1fr;
            gap: 0.3rem;
        }

        @media (max-width: 900px) {
            .pqrs-submission-quick-grid,
            .pqrs-submission-ratings,
            .pqrs-submission-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .pqrs-submission-hero,
            .pqrs-submission-quick-grid,
            .pqrs-submission-grid,
            .pqrs-submission-ratings,
            .pqrs-submission-system .pqrs-submission-data-list,
            .pqrs-submission-extra .pqrs-submission-data-list {
                grid-template-columns: 1fr;
            }

            .pqrs-submission-badge-stack {
                align-items: stretch;
            }

            .pqrs-submission-badge {
                width: 100%;
                white-space: normal;
            }

            .pqrs-submission-data-row {
                grid-template-columns: 1fr;
                gap: 0.32rem;
            }
        }
    </style>

    <section class="pqrs-submission-hero" aria-label="Resumen de la PQRSF">
        <div>
            <p class="pqrs-submission-eyebrow">Expediente PQRSF</p>
            <h3 class="pqrs-submission-title">Solicitud #{{ $summary['id'] ?? '—' }}</h3>
            <p class="pqrs-submission-subtitle">
                Registro recibido en {{ $summary['sede'] ?? '—' }} el {{ $summary['createdAt'] ?? '—' }}.
            </p>
        </div>

        <div class="pqrs-submission-badge-stack" aria-label="Estado y clasificación">
            <span class="pqrs-submission-badge {{ $summary['statusTone'] ?? 'status-neutral' }}">
                {{ $summary['statusLabel'] ?? 'Sin estado' }}
            </span>
            <span class="pqrs-submission-badge {{ $summary['optionTone'] ?? 'option-neutral' }}">
                {{ $summary['optionLabel'] ?? '—' }}
            </span>
        </div>
    </section>

    <section class="pqrs-submission-quick-grid" aria-label="Datos principales">
        <article class="pqrs-submission-quick-card sede">
            <span class="pqrs-submission-quick-label">Sede</span>
            <span class="pqrs-submission-quick-value">{{ $summary['sede'] ?? '—' }}</span>
        </article>
        <article class="pqrs-submission-quick-card tipo">
            <span class="pqrs-submission-quick-label">Tipo</span>
            <span class="pqrs-submission-quick-value">{{ $summary['optionLabel'] ?? '—' }}</span>
        </article>
        <article class="pqrs-submission-quick-card fecha">
            <span class="pqrs-submission-quick-label">Registro</span>
            <span class="pqrs-submission-quick-value">{{ $summary['createdAt'] ?? '—' }}</span>
        </article>
        <article class="pqrs-submission-quick-card promedio {{ $summary['averageTone'] ?? 'rating-empty' }}">
            <span class="pqrs-submission-quick-label">Promedio</span>
            <span class="pqrs-submission-quick-value">{{ $summary['averageRating'] ?? '—' }}</span>
        </article>
    </section>

    <section class="pqrs-submission-grid">
        <article class="pqrs-submission-panel">
            <div class="pqrs-submission-panel-head">
                <h4 class="pqrs-submission-panel-title">Datos del cliente</h4>
                <span class="pqrs-submission-panel-kicker">Contacto</span>
            </div>
            <dl class="pqrs-submission-data-list">
                @foreach ($contactItems as $item)
                    <div class="pqrs-submission-data-row">
                        <dt>{{ $item['label'] }}</dt>
                        <dd>{{ $item['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </article>

        <article class="pqrs-submission-panel">
            <div class="pqrs-submission-panel-head">
                <h4 class="pqrs-submission-panel-title">Detalle de la experiencia</h4>
                <span class="pqrs-submission-panel-kicker">Servicio</span>
            </div>
            <dl class="pqrs-submission-data-list">
                @foreach ($experienceItems as $item)
                    <div class="pqrs-submission-data-row">
                        <dt>{{ $item['label'] }}</dt>
                        <dd>{{ $item['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </article>
    </section>

    <section class="pqrs-submission-panel" aria-label="Calificaciones">
        <div class="pqrs-submission-panel-head">
            <h4 class="pqrs-submission-panel-title">Calificaciones</h4>
            <span class="pqrs-submission-panel-kicker">Escala 1 a 5</span>
        </div>
        <div class="pqrs-submission-ratings">
            @foreach ($ratings as $rating)
                <article class="pqrs-submission-rating-card {{ $rating['tone'] }}">
                    <span class="pqrs-submission-rating-label">{{ $rating['label'] }}</span>
                    <strong class="pqrs-submission-rating-score">{{ $rating['display'] }}</strong>
                    <span class="pqrs-submission-rating-description">{{ $rating['description'] }}</span>
                    <div class="pqrs-submission-meter" aria-hidden="true">
                        <span style="width: {{ $rating['percent'] }}%"></span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="pqrs-submission-observation" aria-label="Observaciones">
        <div class="pqrs-submission-panel-head">
            <h4 class="pqrs-submission-panel-title">Observaciones</h4>
            <span class="pqrs-submission-panel-kicker">Comentario</span>
        </div>
        <p class="pqrs-submission-observation-text">{{ $observations }}</p>
    </section>

    @if ($extraItems !== [])
        <section class="pqrs-submission-system pqrs-submission-extra" aria-label="Campos adicionales">
            <div class="pqrs-submission-panel-head">
                <h4 class="pqrs-submission-panel-title">Campos adicionales</h4>
                <span class="pqrs-submission-panel-kicker">Formulario</span>
            </div>
            <dl class="pqrs-submission-data-list">
                @foreach ($extraItems as $item)
                    <div class="pqrs-submission-data-row">
                        <dt>{{ $item['label'] }}</dt>
                        <dd>{{ $item['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    <section class="pqrs-submission-system" aria-label="Información del sistema">
        <div class="pqrs-submission-panel-head">
            <h4 class="pqrs-submission-panel-title">Información del sistema</h4>
            <span class="pqrs-submission-panel-kicker">Trazabilidad</span>
        </div>
        <dl class="pqrs-submission-data-list">
            @foreach ($systemItems as $item)
                <div class="pqrs-submission-data-row">
                    <dt>{{ $item['label'] }}</dt>
                    <dd>{{ $item['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>
