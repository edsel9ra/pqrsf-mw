<?php

namespace App\Filament\Resources\Sedes\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SedesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                IconColumn::make('activo')
                    ->boolean()
                    ->label('Activo'),
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
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                // Las sedes se desactivan para preservar el histórico PQRSF.
            ]);
    }
}
