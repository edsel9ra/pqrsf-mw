<?php

namespace App\Filament\Resources\SedeComplaintRecipients\Pages;

use App\Filament\Resources\SedeComplaintRecipients\SedeComplaintRecipientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSedeComplaintRecipients extends ListRecords
{
    protected static string $resource = SedeComplaintRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
