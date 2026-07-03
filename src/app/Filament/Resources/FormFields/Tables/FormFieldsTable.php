<?php

namespace App\Filament\Resources\FormFields\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormFieldsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Etiqueta')
                    ->searchable(),
                TextColumn::make('key')
                    ->label('Clave')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'text' => 'Texto',
                        'email' => 'Correo electrónico',
                        'tel' => 'Teléfono',
                        'textarea' => 'Área de texto',
                        'select' => 'Selección',
                        'rating' => 'Calificación',
                        'boolean' => 'Sí/No',
                        'checkbox_list' => 'Lista de casillas',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('orden')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('requerido')
                    ->label('Requerido')
                    ->boolean(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
