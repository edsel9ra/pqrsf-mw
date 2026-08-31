<?php

namespace App\Filament\Resources\ComplaintRecipientProfiles\Schemas;

use App\Models\ComplaintRecipientProfile;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ComplaintRecipientProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->helperText('Se aplica a todas las sedes donde este correo esté registrado para recibir quejas.')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): ?string => ComplaintRecipientProfile::normalizeEmail($state)),
                Select::make('template_key')
                    ->label('Plantilla')
                    ->options(ComplaintRecipientProfile::templateOptions())
                    ->default(ComplaintRecipientProfile::TEMPLATE_FULL)
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        $set(
                            'excluded_field_keys',
                            $state === ComplaintRecipientProfile::TEMPLATE_WITHOUT_CONTACT_DATA
                                ? ComplaintRecipientProfile::withoutContactDataFieldKeys()
                                : [],
                        );
                    })
                    ->required(),
                CheckboxList::make('excluded_field_keys')
                    ->label('Campos que no se enviarán')
                    ->options(ComplaintRecipientProfile::fieldOptions())
                    ->columns(2)
                    ->helperText('Si se excluye algún campo, el correo no incluirá el enlace al PDF completo.'),
            ]);
    }
}
