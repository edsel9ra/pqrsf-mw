<?php

namespace App\Filament\Widgets;

use App\Models\PqrsfSubmission;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Livewire\Attributes\On;

class LatestSubmissions extends TableWidget
{
    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 99;

    public ?array $pageFilters = [];

    #[On('filters-updated')]
    public function onFiltersUpdated($date_from = null, $date_to = null): void
    {
        $this->pageFilters = [
            'date_from' => $date_from,
            'date_to' => $date_to,
        ];
    }

    public function table(Table $table): Table
    {
        $query = PqrsfSubmission::query()->with('sede');

        $from = $this->pageFilters['date_from'] ?? null;
        $to = $this->pageFilters['date_to'] ?? null;
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $table
            ->heading('Últimas PQRSF')
            ->query(
                $query->latest()->limit(10)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('sede.nombre')
                    ->label('Sede')
                    ->sortable(),
                TextColumn::make('field_values')
                    ->label('Cliente')
                    ->getStateUsing(fn ($record) => $record->field_values['nombre_completo'] ?? '—'),
                TextColumn::make('field_values')
                    ->label('Opción')
                    ->getStateUsing(fn ($record) => $record->field_values['opcion_a_calificar'] ?? '—')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Queja' => 'danger',
                        'Reclamo' => 'warning',
                        'Petición' => 'info',
                        'Sugerencia' => 'gray',
                        'Felicitación' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'validated' => 'Validado',
                        'sent' => 'Enviado',
                        default => 'Sin estado',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'validated' => 'info',
                        'sent' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
