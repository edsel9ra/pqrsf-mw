<?php

namespace App\Filament\Resources\PqrsfSubmissions;

use App\Filament\Resources\PqrsfSubmissions\Pages\EditPqrsfSubmission;
use App\Filament\Resources\PqrsfSubmissions\Pages\ListPqrsfSubmissions;
use App\Filament\Resources\PqrsfSubmissions\Tables\PqrsfSubmissionsTable;
use App\Models\PqrsfSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PqrsfSubmissionResource extends Resource
{
    protected static ?string $model = PqrsfSubmission::class;

    protected static ?string $navigationLabel = 'PQRSF';

    protected static ?string $pluralModelLabel = 'PQRSF';

    protected static ?string $modelLabel = 'PQRSF';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return PqrsfSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPqrsfSubmissions::route('/'),
            'edit' => EditPqrsfSubmission::route('/{record}/edit'),
        ];
    }
}
