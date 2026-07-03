<?php

namespace App\Filament\Resources\PqrsfSubmissions\Pages;

use App\Filament\Resources\PqrsfSubmissions\PqrsfSubmissionResource;
use Filament\Resources\Pages\EditRecord;

class EditPqrsfSubmission extends EditRecord
{
    protected static string $resource = PqrsfSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
