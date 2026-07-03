<?php

namespace App\Filament\Resources\SedeRecipients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SedeRecipientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sede_id')
                    ->label('Sede')
                    ->relationship('sede', 'nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required(),
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->required(),
            ]);
    }
}
