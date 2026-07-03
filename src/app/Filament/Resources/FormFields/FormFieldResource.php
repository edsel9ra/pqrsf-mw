<?php

namespace App\Filament\Resources\FormFields;

use App\Filament\Resources\FormFields\Pages\CreateFormField;
use App\Filament\Resources\FormFields\Pages\EditFormField;
use App\Filament\Resources\FormFields\Pages\ListFormFields;
use App\Filament\Resources\FormFields\Schemas\FormFieldForm;
use App\Filament\Resources\FormFields\Tables\FormFieldsTable;
use App\Models\FormField;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FormFieldResource extends Resource
{
    protected static ?string $model = FormField::class;

    protected static ?string $navigationLabel = 'Campos del formulario';

    protected static ?string $pluralModelLabel = 'Campos del formulario';

    protected static ?string $modelLabel = 'Campo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return FormFieldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormFieldsTable::configure($table);
    }

    public static function normalizeFormData(array $data): array
    {
        if (! in_array($data['type'] ?? null, ['select', 'checkbox_list'], true)) {
            $data['options'] = null;

            return $data;
        }

        $data['options'] = collect($data['options'] ?? [])
            ->map(fn (mixed $option): string => trim((string) $option))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $data;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormFields::route('/'),
            'create' => CreateFormField::route('/create'),
            'edit' => EditFormField::route('/{record}/edit'),
        ];
    }
}
