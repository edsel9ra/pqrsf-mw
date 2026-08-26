<?php

namespace App\Filament\Resources\SedeComplaintRecipients\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SedeComplaintRecipientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sede_id')
                    ->label('Sede')
                    ->relationship('sede', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required(),
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->helperText('Opcional. Se mostrará como nombre del destinatario.'),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
