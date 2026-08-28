<style>
    .pqrs-report-page {
        --pqrs-ink: #1f2937;
        --pqrs-muted: #6b7280;
        --pqrs-line: rgba(148, 163, 184, 0.24);
        --pqrs-card: rgba(255, 255, 255, 0.96);
        --pqrs-amber: #d97706;
        display: grid;
        gap: 1.25rem;
        width: 100%;
        min-width: 0;
        max-width: 100%;
        overflow: visible;
    }

    .pqrs-report-page *,
    .pqrs-report-page *::before,
    .pqrs-report-page *::after {
        box-sizing: border-box;
    }

    .pqrs-report-page [x-cloak] {
        display: none !important;
    }

    .pqrs-report-page :is(.fi-fo-component-ctn, .fi-fo-field-wrp, .fi-input-wrp, .fi-select-input, .fi-input) {
        min-width: 0;
        max-width: 100%;
    }

    .pqrs-report-shell,
    .pqrs-report-card,
    .pqrs-report-table-card,
    .pqrs-report-empty {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        background: var(--pqrs-card);
        border: 1px solid var(--pqrs-line);
        border-radius: 1.1rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.05);
    }

    .pqrs-report-shell,
    .pqrs-report-card,
    .pqrs-report-table-card,
    .pqrs-report-empty {
        padding: clamp(0.85rem, 2.5vw, 1.1rem);
    }

    .pqrs-report-filter-head,
    .pqrs-report-toolbar,
    .pqrs-report-section-head,
    .pqrs-report-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-width: 0;
        max-width: 100%;
    }

    .pqrs-report-filter-head,
    .pqrs-report-toolbar {
        flex-wrap: wrap;
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

    .pqrs-report-result {
        display: grid;
        gap: 1.25rem;
        width: 100%;
        min-width: 0;
        max-width: 100%;
    }

    .pqrs-report-tabs {
        display: grid;
        gap: 0.85rem;
        width: 100%;
        min-width: 0;
        max-width: 100%;
    }

    .pqrs-report-tabs-list {
        max-width: 100%;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        scrollbar-color: rgba(148, 163, 184, 0.55) transparent;
        scrollbar-width: thin;
    }

    .pqrs-report-tabs-list .fi-tabs-item {
        flex: 0 0 auto;
        max-width: min(22rem, 78vw);
    }

    .pqrs-report-tabs-list .fi-tabs-item-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pqrs-report-tab-panel {
        min-width: 0;
        max-width: 100%;
    }

    .pqrs-report-toolbar {
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
    }

    .pqrs-report-stat {
        position: relative;
        min-height: 7.2rem;
        overflow: hidden;
        padding: 1rem;
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border: 1px solid var(--pqrs-line);
        border-radius: 1rem;
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

    .pqrs-report-stat.total {
        color: #334155;
    }

    .pqrs-report-stat-label,
    .pqrs-report-stat-note {
        display: block;
        color: var(--pqrs-muted);
    }

    .pqrs-report-stat-label {
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
        margin-top: 0.7rem;
        font-size: 0.78rem;
    }

    .pqrs-report-table-card {
        overflow: hidden;
    }

    .pqrs-report-table-wrap {
        width: 100%;
        margin-top: 0.95rem;
        overflow-x: auto;
        border: 1px solid var(--pqrs-line);
        border-radius: 0.85rem;
    }

    .pqrs-report-table {
        width: 100%;
        min-width: 42rem;
        border-collapse: collapse;
        color: var(--pqrs-ink);
        font-size: 0.86rem;
    }

    .pqrs-report-table th,
    .pqrs-report-table td {
        padding: 0.75rem 0.8rem;
        text-align: left;
        vertical-align: top;
        border-bottom: 1px solid var(--pqrs-line);
    }

    .pqrs-report-table th {
        color: #475569;
        background: #f8fafc;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .pqrs-report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .pqrs-report-table tbody tr:nth-child(even) {
        background: rgba(248, 250, 252, 0.62);
    }

    .pqrs-report-table td {
        overflow-wrap: anywhere;
    }

    .pqrs-report-observation {
        min-width: 15rem;
        white-space: pre-line;
    }

    .pqrs-report-group + .pqrs-report-group {
        margin-top: 1.25rem;
    }

    .pqrs-report-group-total {
        color: var(--pqrs-muted);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .pqrs-report-empty {
        text-align: center;
    }

    .pqrs-report-footer {
        align-items: flex-start;
        flex-wrap: wrap;
        padding: 0 0.25rem;
    }

    @media (max-width: 720px) {
        .pqrs-report-filter-head,
        .pqrs-report-toolbar,
        .pqrs-report-section-head,
        .pqrs-report-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .pqrs-report-actions,
        .pqrs-report-actions > * {
            width: 100%;
        }

        .pqrs-report-actions > * {
            flex-basis: 100%;
        }

        .pqrs-report-table-wrap {
            overflow: visible;
            border: 0;
        }

        .pqrs-report-table {
            min-width: 0;
        }

        .pqrs-report-table thead {
            display: none;
        }

        .pqrs-report-table,
        .pqrs-report-table tbody,
        .pqrs-report-table tr,
        .pqrs-report-table td {
            display: block;
            width: 100%;
        }

        .pqrs-report-table tr {
            padding: 0.65rem 0;
            border-bottom: 1px solid var(--pqrs-line);
        }

        .pqrs-report-table tr:last-child {
            border-bottom: 0;
        }

        .pqrs-report-table td {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.45rem 0;
            text-align: right;
            border: 0;
        }

        .pqrs-report-table td::before {
            flex: 0 0 42%;
            color: var(--pqrs-muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-align: left;
            text-transform: uppercase;
            content: attr(data-label);
        }

        .pqrs-report-table td.pqrs-report-observation {
            display: block;
            text-align: left;
        }

        .pqrs-report-table td.pqrs-report-observation::before {
            display: block;
            margin-bottom: 0.35rem;
        }
    }
</style>
