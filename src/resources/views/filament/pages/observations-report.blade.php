<x-filament-panels::page>
    @include('filament.pages.partials.detailed-report-styles')

    <div class="pqrs-report-page">
        <section class="pqrs-report-shell">
            <div class="pqrs-report-filter-head">
                <div>
                    <p class="pqrs-report-eyebrow">Seguimiento cualitativo</p>
                    <h2 class="pqrs-report-title">Filtros del reporte</h2>
                </div>
                <span class="pqrs-report-subtitle">Seleccione criterios y use Generar reporte.</span>
            </div>

            {{ $this->form }}
        </section>

        @if ($showReport)
            @php
                $groups = collect($reportData['groups'] ?? []);
                $filterLabels = $reportData['filterLabels'] ?? [];
            @endphp

            <div class="pqrs-report-result">
                <div class="pqrs-report-toolbar">
                    <div>
                        <p class="pqrs-report-eyebrow">Resultado generado</p>
                        <h2 class="pqrs-report-title">Observaciones por sede</h2>
                    </div>
                    <div class="pqrs-report-actions">
                        <x-filament::button
                            tag="a"
                            href="{{ $this->getDownloadUrl('pdf') }}"
                            icon="heroicon-o-document-arrow-down"
                            color="danger"
                            target="_blank"
                            rel="noopener"
                        >
                            Descargar PDF
                        </x-filament::button>
                        <x-filament::button
                            tag="a"
                            href="{{ $this->getDownloadUrl('xlsx') }}"
                            icon="heroicon-o-table-cells"
                            color="success"
                        >
                            Descargar XLSX
                        </x-filament::button>
                    </div>
                </div>

                @if ($groups->isEmpty())
                    <section class="pqrs-report-empty">
                        <p class="pqrs-report-eyebrow">Sin observaciones</p>
                        <h2 class="pqrs-report-title">No se encontraron observaciones con los filtros seleccionados.</h2>
                    </section>
                @else
                    <div class="pqrs-report-stats-grid">
                        <article class="pqrs-report-stat total">
                            <span class="pqrs-report-stat-label">Total de observaciones</span>
                            <strong class="pqrs-report-stat-value">{{ $reportData['total'] }}</strong>
                            <span class="pqrs-report-stat-note">Registros con observaciones diligenciadas</span>
                        </article>
                    </div>

                    @foreach ($groups as $group)
                        <section class="pqrs-report-table-card pqrs-report-group">
                            <div class="pqrs-report-section-head">
                                <div>
                                    <p class="pqrs-report-eyebrow">Sede</p>
                                    <h3 class="pqrs-report-title">{{ $group['sede'] }}</h3>
                                </div>
                                <span class="pqrs-report-group-total">{{ $group['total'] }} observación(es)</span>
                            </div>

                            <div class="pqrs-report-table-wrap">
                                <table class="pqrs-report-table">
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
                                                <td data-label="Nombre Completo"><strong>{{ $row['nombre_completo'] }}</strong></td>
                                                <td data-label="Opción Calificada">{{ $row['opcion_calificada'] }}</td>
                                                <td data-label="Observaciones" class="pqrs-report-observation">{{ $row['observaciones'] }}</td>
                                                <td data-label="Nombre de Mesero">{{ $row['nombre_mesero'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endforeach
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
