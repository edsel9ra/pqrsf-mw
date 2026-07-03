<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Resultados por sede (porcentajes)
        </x-slot>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 0.75rem 1rem; text-align: left; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Sede</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Atención a la Mesa</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Calidad de la Comida</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Tiempo de Entrega</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Ambientación</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Total General</th>
                    </tr>
                </thead>
                <tbody>
                        @forelse ($rows as $row)
                        <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.16);">
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: #1f2937;">{{ $row['sede'] }}</td>
                            @php $pctFields = ['atencion', 'comida', 'tiempo', 'ambientacion', 'promedio']; @endphp
                            @foreach ($pctFields as $field)
                                @php $isLow = $row[$field] < 98; @endphp
                                <td style="padding: 0.75rem 1rem; text-align: center; {{ $isLow ? 'color: #dc2626; font-weight: 700;' : 'color: #1f2937;' }}">{{ number_format($row[$field], 2) }}%</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: #9ca3af;">Sin datos</td>
                        </tr>
                    @endforelse
                </tbody>
                @if (!empty($grandTotal))
                    <tfoot>
                        <tr style="border-top: 2px solid #d1d5db; background: #f8fafc;">
                            <td style="padding: 0.85rem 1rem; font-weight: 800; color: #111827;">TOTAL GENERAL</td>
                            <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #111827;">{{ number_format($grandTotal['atencion'], 2) }}%</td>
                            <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #111827;">{{ number_format($grandTotal['comida'], 2) }}%</td>
                            <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #111827;">{{ number_format($grandTotal['tiempo'], 2) }}%</td>
                            <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #111827;">{{ number_format($grandTotal['ambientacion'], 2) }}%</td>
                            <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #111827;">{{ number_format($grandTotal['promedio'], 2) }}%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
