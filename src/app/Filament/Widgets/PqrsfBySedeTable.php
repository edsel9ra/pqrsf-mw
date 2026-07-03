<?php

namespace App\Filament\Widgets;

use App\Models\PqrsfSubmission;
use App\Models\Sede;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class PqrsfBySedeTable extends Widget
{
    protected string $view = 'filament.widgets.pqrsf-by-sede-table';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 98;

    public ?array $pageFilters = [];

    public array $rows = [];

    public array $optionLabels = [];

    public array $grandTotal = [];

    #[On('filters-updated')]
    public function onFiltersUpdated($date_from = null, $date_to = null): void
    {
        $this->pageFilters = [
            'date_from' => $date_from,
            'date_to' => $date_to,
        ];
        $this->loadData();
    }

    public function mount(): void
    {
        $this->loadData();
    }

    protected function applyDateFilter($query)
    {
        $from = $this->pageFilters['date_from'] ?? null;
        $to = $this->pageFilters['date_to'] ?? null;
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    protected function loadData(): void
    {
        $sedes = Sede::orderBy('nombre')->pluck('nombre', 'id');
        $this->optionLabels = ['Felicitación', 'Queja', 'Reclamo', 'Sugerencia', 'Petición'];

        $base = PqrsfSubmission::query();
        $base = $this->applyDateFilter($base);

        $rows = (clone $base)
            ->select(
                'sede_id',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(field_values, '$.opcion_a_calificar')) as opcion"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('sede_id', 'opcion')
            ->get()
            ->groupBy('sede_id');

        $data = [];
        $grandTotals = array_fill_keys($this->optionLabels, 0);
        $grandTotalCount = 0;

        foreach ($sedes as $id => $nombre) {
            $sedeRows = $rows->get($id, collect());
            $row = ['sede' => $nombre];
            $rowTotal = 0;
            foreach ($this->optionLabels as $opt) {
                $count = (int) $sedeRows->firstWhere('opcion', $opt)?->total ?? 0;
                $row[$opt] = $count;
                $rowTotal += $count;
                $grandTotals[$opt] += $count;
            }
            $row['total'] = $rowTotal;
            $grandTotalCount += $rowTotal;
            $data[] = $row;
        }

        $this->rows = $data;
        $this->grandTotal = $grandTotals;
        $this->grandTotal['total'] = $grandTotalCount;
    }
}
