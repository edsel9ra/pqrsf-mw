<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            PQRSF por sede
        </x-slot>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 0.75rem 1rem; text-align: left; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Sede</th>
                        @foreach ($optionLabels as $opt)
                            <th style="padding: 0.75rem 1rem; text-align: center; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">{{ strtoupper($opt) }}</th>
                        @endforeach
                        <th style="padding: 0.75rem 1rem; text-align: center; color: #6b7280; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: #f8fafc;">Total General</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.16);">
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: #1f2937;">{{ $row['sede'] }}</td>
                            @foreach ($optionLabels as $opt)
                                <td style="padding: 0.75rem 1rem; text-align: center; color: #1f2937;">{{ $row[$opt] }}</td>
                            @endforeach
                            <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 700; color: #1f2937;">{{ $row['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #d1d5db; background: #f8fafc;">
                        <td style="padding: 0.85rem 1rem; font-weight: 800; color: #111827;">TOTAL GENERAL</td>
                        @foreach ($optionLabels as $opt)
                            <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #111827;">{{ $grandTotal[$opt] }}</td>
                        @endforeach
                        <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #111827;">{{ $grandTotal['total'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
