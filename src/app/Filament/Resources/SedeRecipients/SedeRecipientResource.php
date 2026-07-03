<?php

namespace App\Filament\Resources\SedeRecipients;

use App\Filament\Resources\SedeRecipients\Pages\CreateSedeRecipient;
use App\Filament\Resources\SedeRecipients\Pages\EditSedeRecipient;
use App\Filament\Resources\SedeRecipients\Pages\ListSedeRecipients;
use App\Filament\Resources\SedeRecipients\Schemas\SedeRecipientForm;
use App\Filament\Resources\SedeRecipients\Tables\SedeRecipientsTable;
use App\Models\SedeRecipient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SedeRecipientResource extends Resource
{
    protected static ?string $model = SedeRecipient::class;

    protected static ?string $navigationLabel = 'Destinatarios';

    protected static ?string $pluralModelLabel = 'Destinatarios';

    protected static ?string $modelLabel = 'Destinatario';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return SedeRecipientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SedeRecipientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSedeRecipients::route('/'),
            'create' => CreateSedeRecipient::route('/create'),
            'edit' => EditSedeRecipient::route('/{record}/edit'),
        ];
    }
}
