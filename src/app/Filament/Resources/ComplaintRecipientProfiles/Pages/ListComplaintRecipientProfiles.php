<?php

namespace App\Filament\Resources\ComplaintRecipientProfiles\Pages;

use App\Filament\Resources\ComplaintRecipientProfiles\ComplaintRecipientProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComplaintRecipientProfiles extends ListRecords
{
    protected static string $resource = ComplaintRecipientProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
