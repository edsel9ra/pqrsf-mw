<?php

namespace App\Filament\Resources\SubmissionLogs\Pages;

use App\Filament\Resources\SubmissionLogs\SubmissionLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSubmissionLogs extends ListRecords
{
    protected static string $resource = SubmissionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
