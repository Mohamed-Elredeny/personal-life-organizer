<?php

namespace App\Filament\Resources\AnniversaryResource\Pages;

use App\Filament\Resources\AnniversaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnniversary extends EditRecord
{
    protected static string $resource = AnniversaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
