<?php

namespace App\Filament\Resources\SedeComplaintRecipients\Pages;

use App\Filament\Resources\SedeComplaintRecipients\SedeComplaintRecipientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSedeComplaintRecipient extends EditRecord
{
    protected static string $resource = SedeComplaintRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
