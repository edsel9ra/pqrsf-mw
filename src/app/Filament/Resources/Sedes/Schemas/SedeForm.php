<?php

namespace App\Filament\Resources\Sedes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SedeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('direccion')
                    ->label('Dirección'),
                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->required(),
            ]);
    }
}
