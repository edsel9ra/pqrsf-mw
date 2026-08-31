<?php

namespace App\Filament\Resources\ComplaintRecipientProfiles\Pages;

use App\Filament\Resources\ComplaintRecipientProfiles\ComplaintRecipientProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComplaintRecipientProfile extends EditRecord
{
    protected static string $resource = ComplaintRecipientProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
