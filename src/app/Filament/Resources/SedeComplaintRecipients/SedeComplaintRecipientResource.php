<?php

namespace App\Filament\Resources\SedeComplaintRecipients;

use App\Filament\Resources\SedeComplaintRecipients\Pages\CreateSedeComplaintRecipient;
use App\Filament\Resources\SedeComplaintRecipients\Pages\EditSedeComplaintRecipient;
use App\Filament\Resources\SedeComplaintRecipients\Pages\ListSedeComplaintRecipients;
use App\Filament\Resources\SedeComplaintRecipients\Schemas\SedeComplaintRecipientForm;
use App\Filament\Resources\SedeComplaintRecipients\Tables\SedeComplaintRecipientsTable;
use App\Models\SedeComplaintRecipient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SedeComplaintRecipientResource extends Resource
{
    protected static ?string $model = SedeComplaintRecipient::class;

    protected static ?string $navigationLabel = 'Destinatarios de quejas';

    protected static ?string $pluralModelLabel = 'Destinatarios de quejas';

    protected static ?string $modelLabel = 'Destinatario de queja';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return SedeComplaintRecipientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SedeComplaintRecipientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSedeComplaintRecipients::route('/'),
            'create' => CreateSedeComplaintRecipient::route('/create'),
            'edit' => EditSedeComplaintRecipient::route('/{record}/edit'),
        ];
    }
}
