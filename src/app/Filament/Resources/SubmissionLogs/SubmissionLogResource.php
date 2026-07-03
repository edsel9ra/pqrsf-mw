<?php

namespace App\Filament\Resources\SubmissionLogs;

use App\Filament\Resources\SubmissionLogs\Pages\ListSubmissionLogs;
use App\Filament\Resources\SubmissionLogs\Tables\SubmissionLogsTable;
use App\Models\SubmissionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubmissionLogResource extends Resource
{
    protected static ?string $model = SubmissionLog::class;

    protected static ?string $navigationLabel = 'Historial';

    protected static ?string $pluralModelLabel = 'Historial';

    protected static ?string $modelLabel = 'Registro de historial';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return SubmissionLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubmissionLogs::route('/'),
        ];
    }
}
