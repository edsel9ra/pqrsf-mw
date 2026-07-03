<?php

namespace App\Filament\Resources\SubmissionLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubmissionLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('submission_id')->label('PQRSF #')->sortable()->searchable(),
                TextColumn::make('user.name')->label('Usuario')->searchable(),
                TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'validated' => 'Validado',
                        'sent' => 'Enviado',
                        'send_failed' => 'Envío fallido',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'validated' => 'success',
                        'sent' => 'info',
                        'send_failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('notas')->label('Notas')->limit(50),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([])
            ->toolbarActions([])
            ->recordUrl(null);
    }
}
