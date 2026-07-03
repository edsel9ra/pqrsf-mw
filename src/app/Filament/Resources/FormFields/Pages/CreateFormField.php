<?php

namespace App\Filament\Resources\FormFields\Pages;

use App\Filament\Resources\FormFields\FormFieldResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormField extends CreateRecord
{
    protected static string $resource = FormFieldResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return FormFieldResource::normalizeFormData($data);
    }
}
