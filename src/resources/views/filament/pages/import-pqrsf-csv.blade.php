<x-filament-panels::page>
    <div class="grid gap-6">
        <x-filament::section>
            <x-slot name="heading">
                Archivo de importación
            </x-slot>

            <div class="grid gap-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Suba el CSV exportado desde Microsoft Forms, analícelo y confirme la importación solo cuando el resumen no tenga errores.
                </p>

                {{ $this->form }}
            </div>
        </x-filament::section>

        @if ($summary)
            @php
                $errors = $summary['errors'] ?? [];
                $missingSedes = $summary['missing_sedes'] ?? [];
                $stats = [
                    'Registros leídos' => $summary['records_count'] ?? 0,
                    'Registros nuevos' => $summary['new_count'] ?? 0,
                    'Duplicados omitidos' => $summary['duplicate_count'] ?? 0,
                    'Sedes nuevas' => $summary['missing_sedes_count'] ?? 0,
                    'Importados' => $summary['imported'] ?? 0,
                    'Sedes creadas' => $summary['created_sedes'] ?? 0,
                ];
            @endphp

            <x-filament::section>
                <x-slot name="heading">
                    Resumen del análisis
                </x-slot>

                <div class="grid gap-4">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($stats as $label => $value)
                            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                <strong class="mt-2 block text-2xl font-bold text-gray-950 dark:text-white">{{ number_format((int) $value) }}</strong>
                            </div>
                        @endforeach
                    </div>

                    @if ($errors !== [])
                        <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-800 dark:border-danger-900 dark:bg-danger-950 dark:text-danger-200">
                            <strong class="block text-base">El CSV no se pudo normalizar por completo.</strong>
                            <p class="mt-1">Corrija estos errores y vuelva a analizar el archivo.</p>

                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-danger-200 dark:border-danger-900">
                                            <th class="py-2 pr-4 font-semibold">Línea</th>
                                            <th class="py-2 pr-4 font-semibold">Error</th>
                                            <th class="py-2 pr-4 font-semibold">Columnas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (array_slice($errors, 0, 20) as $error)
                                            <tr class="border-b border-danger-100 last:border-0 dark:border-danger-900/60">
                                                <td class="py-2 pr-4">{{ $error[0] ?? '-' }}</td>
                                                <td class="py-2 pr-4">{{ $error[1] ?? 'Error desconocido' }}</td>
                                                <td class="py-2 pr-4">{{ $error[2] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if (count($errors) > 20)
                                <p class="mt-3">Errores restantes: {{ number_format(count($errors) - 20) }}</p>
                            @endif
                        </div>
                    @elseif (($summary['imported'] ?? 0) > 0)
                        <div class="rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-800 dark:border-success-900 dark:bg-success-950 dark:text-success-200">
                            Importación completada. Los registros nuevos quedaron guardados con el estado seleccionado.
                        </div>
                    @elseif ($readyToImport)
                        <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800 dark:border-primary-900 dark:bg-primary-950 dark:text-primary-200">
                            El análisis no encontró errores. Use <strong>Importar registros</strong> para guardar los registros nuevos.
                        </div>
                    @else
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                            No hay registros nuevos para importar.
                        </div>
                    @endif

                    @if ($missingSedes !== [])
                        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-900 dark:border-warning-900 dark:bg-warning-950 dark:text-warning-100">
                            <strong class="block">Sedes que se crearán</strong>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach (array_slice($missingSedes, 0, 30) as $sede)
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-warning-900 ring-1 ring-warning-200 dark:bg-warning-900 dark:text-warning-100 dark:ring-warning-800">{{ $sede }}</span>
                                @endforeach
                            </div>

                            @if (count($missingSedes) > 30)
                                <p class="mt-3">Sedes restantes: {{ number_format(count($missingSedes) - 30) }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
