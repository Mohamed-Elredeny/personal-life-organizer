<?php

namespace App\Filament\Resources\GratitudeLogResource\Pages;

use App\Filament\Resources\GratitudeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGratitudeLog extends EditRecord
{
    protected static string $resource = GratitudeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
