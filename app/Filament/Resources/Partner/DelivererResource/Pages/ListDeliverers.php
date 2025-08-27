<?php

namespace App\Filament\Resources\Partner\DelivererResource\Pages;

use App\Filament\Resources\Partner\DelivererResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliverers extends ListRecords
{
    protected static string $resource = DelivererResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
