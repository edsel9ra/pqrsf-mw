<?php

namespace App\Filament\Resources\FormFields\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FormFieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Etiqueta')
                    ->required(),
                TextInput::make('key')
                    ->label('Clave')
                    ->helperText('Usa solo minúsculas, números y guiones bajos. Ejemplo: nombre_completo')
                    ->required()
                    ->regex('/^[a-z0-9_]+$/')
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'text' => 'Texto',
                        'email' => 'Correo electrónico',
                        'tel' => 'Teléfono',
                        'textarea' => 'Área de texto',
                        'select' => 'Selección',
                        'rating' => 'Calificación (1-5)',
                        'boolean' => 'Sí/No',
                        'checkbox_list' => 'Lista de casillas',
                    ])
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if (! in_array($state, ['select', 'checkbox_list'], true)) {
                            $set('options', null);
                        }
                    })
                    ->required(),
                TagsInput::make('options')
                    ->label('Opciones')
                    ->placeholder('Escriba una opción y presione Enter')
                    ->helperText('Aplica únicamente para Selección y Lista de casillas.')
                    ->reorderable()
                    ->nestedRecursiveRules(['string', 'max:255'])
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['select', 'checkbox_list'], true))
                    ->dehydrateStateUsing(fn (?array $state): array => collect($state ?? [])
                        ->map(fn (mixed $option): string => trim((string) $option))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all())
                    ->nullable(),
                Textarea::make('validation_rules')
                    ->label('Reglas de validación (JSON)')
                    ->json()
                    ->nullable(),
                TextInput::make('orden')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('requerido')
                    ->label('Requerido'),
                Toggle::make('activo')
                    ->label('Activo'),
            ]);
    }
}
