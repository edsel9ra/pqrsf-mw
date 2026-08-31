<?php

namespace App\Filament\Resources\ComplaintRecipientProfiles;

use App\Filament\Resources\ComplaintRecipientProfiles\Pages\CreateComplaintRecipientProfile;
use App\Filament\Resources\ComplaintRecipientProfiles\Pages\EditComplaintRecipientProfile;
use App\Filament\Resources\ComplaintRecipientProfiles\Pages\ListComplaintRecipientProfiles;
use App\Filament\Resources\ComplaintRecipientProfiles\Schemas\ComplaintRecipientProfileForm;
use App\Filament\Resources\ComplaintRecipientProfiles\Tables\ComplaintRecipientProfilesTable;
use App\Models\ComplaintRecipientProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ComplaintRecipientProfileResource extends Resource
{
    protected static ?string $model = ComplaintRecipientProfile::class;

    protected static ?string $navigationLabel = 'Plantillas de quejas';

    protected static ?string $pluralModelLabel = 'Plantillas de quejas';

    protected static ?string $modelLabel = 'Plantilla de queja';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ComplaintRecipientProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplaintRecipientProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComplaintRecipientProfiles::route('/'),
            'create' => CreateComplaintRecipientProfile::route('/create'),
            'edit' => EditComplaintRecipientProfile::route('/{record}/edit'),
        ];
    }
}
