<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de observaciones PQRSF</title>
    <style>
        @page { margin: 28px 30px 32px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #27313a;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.45;
        }
        table { width: 100%; border-collapse: collapse; }
        .header {
            margin-bottom: 16px;
            padding: 17px 20px 16px;
            border-left: 6px solid #d49a3a;
            background: #211a17;
            color: #fff;
        }
        .header-table td { vertical-align: middle; }
        .header-content { padding-right: 18px; }
        .header-side { width: 130px; text-align: right; }
        .header-side img { width: 92px; height: auto; padding: 7px; background: #fff; }
        .eyebrow {
            margin: 0 0 5px;
            color: #dca855;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }
        h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.1; }
        .description { margin: 6px 0 0; color: #eadfce; font-size: 9px; }
        .meta { margin-top: 8px; color: #eadfce; font-size: 8px; }
        .filter-list { margin-top: 8px; color: #fff; font-size: 8px; }
        .filter-list span { display: inline-block; margin: 0 4px 3px 0; padding: 3px 6px; background: #493b31; }
        .summary-box {
            margin-bottom: 14px;
            padding: 11px 13px;
            border-left: 4px solid #d49a3a;
            background: #f7f1e8;
            color: #4b4038;
        }
        .summary-number { color: #211a17; font-size: 18px; font-weight: bold; }
        .group { margin-bottom: 18px; page-break-inside: avoid; }
        .group-heading {
            padding: 9px 11px;
            border: 1px solid #e5ddd2;
            border-bottom: 0;
            background: #f7f1e8;
        }
        .group-heading strong { color: #211a17; font-size: 13px; }
        .group-heading span { float: right; color: #756b62; font-size: 8px; }
        .data-table { page-break-inside: auto; }
        .data-table thead { display: table-header-group; }
        .data-table th,
        .data-table td {
            padding: 8px 9px;
            border: 1px solid #e5ddd2;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        .data-table th {
            background: #211a17;
            color: #fff;
            font-size: 7px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .data-table tr { page-break-inside: avoid; }
        .observation { white-space: pre-line; }
        .empty { padding: 18px; border: 1px solid #e5ddd2; color: #6b625a; text-align: center; }
        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #ded5ca;
            color: #756b62;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <table class="header-table">
            <tr>
                <td class="header-content">
                    <p class="eyebrow">Seguimiento cualitativo</p>
                    <h1>Reporte de observaciones por sede</h1>
                    <p class="description">Comentarios de clientes agrupados por sede.</p>
                    <div class="meta">Generado el {{ $generatedAt }}</div>
                    <div class="filter-list">
                        @forelse ($filterLabels as $label)
                            <span>{{ $label }}</span>
                        @empty
                            <span>Todos los registros disponibles</span>
                        @endforelse
                    </div>
                </td>
                @if (! empty($logoSrc ?? ''))
                    <td class="header-side">
                        <img src="{{ $logoSrc }}" alt="Logo MW">
                    </td>
                @endif
            </tr>
        </table>
    </header>

    <div class="summary-box"><span class="summary-number">{{ $total }}</span> observación(es) encontradas en el periodo seleccionado.</div>

    @forelse ($groups as $group)
        <section class="group">
            <div class="group-heading">
                <strong>{{ $group['sede'] }}</strong>
                <span>{{ $group['total'] }} observación(es)</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Opción Calificada</th>
                        <th>Observaciones</th>
                        <th>Nombre de Mesero</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group['rows'] as $row)
                        <tr>
                            <td><strong>{{ $row['nombre_completo'] }}</strong></td>
                            <td>{{ $row['opcion_calificada'] }}</td>
                            <td class="observation">{{ $row['observaciones'] }}</td>
                            <td>{{ $row['nombre_mesero'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @empty
        <div class="empty">No se encontraron observaciones con los filtros seleccionados.</div>
    @endforelse

    <div class="footer">Reporte generado el {{ $generatedAt }} - Sistema PQRSF - {{ $total }} observaciones</div>
</body>
</html>
