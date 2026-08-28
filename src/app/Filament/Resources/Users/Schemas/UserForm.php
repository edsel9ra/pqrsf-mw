<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->unique()
                    ->maxLength(255),
                Select::make('role')
                    ->label('Rol')
                    ->options([
                        'user' => 'Usuario de consulta',
                        'admin' => 'Administrador',
                    ])
                    ->default('user')
                    ->required()
                    ->in(['user', 'admin'])
                    ->native(false),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->confirmed()
                    ->autocomplete('new-password'),
                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrated(false)
                    ->autocomplete('new-password'),
            ])
            ->columns([
                'default' => 1,
                'md' => 2,
            ]);
    }
}
