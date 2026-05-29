<?php

namespace App\Filament\Resources\GratitudeLogResource\Pages;

use App\Filament\Resources\GratitudeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGratitudeLogs extends ListRecords
{
    protected static string $resource = GratitudeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
