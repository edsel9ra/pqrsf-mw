<?php

namespace App\Filament\Resources\SedeRecipients\Pages;

use App\Filament\Resources\SedeRecipients\SedeRecipientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSedeRecipients extends ListRecords
{
    protected static string $resource = SedeRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
