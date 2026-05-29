<?php

namespace App\Filament\Resources\AnniversaryResource\Pages;

use App\Filament\Resources\AnniversaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnniversaries extends ListRecords
{
    protected static string $resource = AnniversaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
