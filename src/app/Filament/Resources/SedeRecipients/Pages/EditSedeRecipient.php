<?php

namespace App\Filament\Resources\SedeRecipients\Pages;

use App\Filament\Resources\SedeRecipients\SedeRecipientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSedeRecipient extends EditRecord
{
    protected static string $resource = SedeRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
