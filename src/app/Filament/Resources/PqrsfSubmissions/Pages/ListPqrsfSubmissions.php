<?php

namespace App\Filament\Resources\PqrsfSubmissions\Pages;

use App\Filament\Resources\PqrsfSubmissions\PqrsfSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListPqrsfSubmissions extends ListRecords
{
    protected static string $resource = PqrsfSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
