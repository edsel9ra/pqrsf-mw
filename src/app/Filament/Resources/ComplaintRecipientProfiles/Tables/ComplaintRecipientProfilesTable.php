<?php

namespace App\Filament\Resources\ComplaintRecipientProfiles\Tables;

use App\Models\ComplaintRecipientProfile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComplaintRecipientProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template_key')
                    ->label('Plantilla')
                    ->formatStateUsing(fn (?string $state): string => ComplaintRecipientProfile::templateOptions()[$state] ?? ($state ?: '—')),
                TextColumn::make('excluded_field_keys')
                    ->label('Campos excluidos')
                    ->getStateUsing(fn (ComplaintRecipientProfile $record): string => collect($record->effectiveExcludedFieldKeys())
                        ->map(fn (string $key): string => ComplaintRecipientProfile::fieldOptions()[$key] ?? $key)
                        ->implode(', ') ?: 'Ninguno')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
