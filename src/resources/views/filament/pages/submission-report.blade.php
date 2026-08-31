<x-filament-panels::page>
    @include('filament.pages.partials.detailed-report-styles')

    <div class="pqrs-report-page">
        <section class="pqrs-report-shell">
            <div class="pqrs-report-filter-head">
                <div>
                    <p class="pqrs-report-eyebrow">Detalle operativo</p>
                    <h2 class="pqrs-report-title">Filtros del reporte</h2>
                </div>
                <span class="pqrs-report-subtitle">Seleccione criterios y use Generar reporte.</span>
            </div>

            {{ $this->form }}
        </section>

        @if ($showReport)
            @php
                $rows = collect($reportData['rows'] ?? []);
                $filterLabels = $reportData['filterLabels'] ?? [];
            @endphp

            <div class="pqrs-report-result">
                <div class="pqrs-report-toolbar">
                    <div>
                        <p class="pqrs-report-eyebrow">Resultado generado</p>
                        <h2 class="pqrs-report-title">Registros PQRSF</h2>
                    </div>
                    <div class="pqrs-report-actions">
                        <x-filament::button
                            tag="a"
                            :href="$this->getDownloadUrl('pdf')"
                            icon="heroicon-o-document-arrow-down"
                            color="danger"
                            target="_blank"
                            rel="noopener"
                        >
                            Descargar PDF
                        </x-filament::button>
                        <x-filament::button
                            tag="a"
                            :href="$this->getDownloadUrl('xlsx')"
                            icon="heroicon-o-table-cells"
                            color="success"
                        >
                            Descargar XLSX
                        </x-filament::button>
                    </div>
                </div>

                @if ($rows->isEmpty())
                    <section class="pqrs-report-empty">
                        <p class="pqrs-report-eyebrow">Sin resultados</p>
                        <h2 class="pqrs-report-title">No se encontraron datos con los filtros seleccionados.</h2>
                    </section>
                @else
                    <div class="pqrs-report-stats-grid">
                        <article class="pqrs-report-stat total">
                            <span class="pqrs-report-stat-label">Total de registros</span>
                            <strong class="pqrs-report-stat-value">{{ $reportData['total'] }}</strong>
                            <span class="pqrs-report-stat-note">Encuestas del periodo</span>
                        </article>
                    </div>

                    <section class="pqrs-report-table-card">
                        <div class="pqrs-report-section-head">
                            <div>
                                <p class="pqrs-report-eyebrow">Listado filtrado</p>
                                <h3 class="pqrs-report-title">Detalle de respuestas</h3>
                            </div>
                        </div>

                        <div class="pqrs-report-table-wrap">
                            <table class="pqrs-report-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Sede</th>
                                        <th>Nombre Completo</th>
                                        <th>Nombre de Mesero</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr>
                                            <td data-label="Fecha">{{ $row['fecha'] }}</td>
                                            <td data-label="Sede">{{ $row['sede'] }}</td>
                                            <td data-label="Nombre Completo"><strong>{{ $row['nombre_completo'] }}</strong></td>
                                            <td data-label="Nombre de Mesero">{{ $row['nombre_mesero'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                <footer class="pqrs-report-footer">
                    <span class="pqrs-report-filters">
                        @if ($filterLabels !== [])
                            Filtros: {{ implode(' | ', $filterLabels) }}
                        @else
                            Filtros: Todos los registros disponibles
                        @endif
                    </span>
                    <span class="pqrs-report-generated">Generado: {{ $reportData['generatedAt'] }}</span>
                </footer>
            </div>
        @endif
    </div>
</x-filament-panels::page>
