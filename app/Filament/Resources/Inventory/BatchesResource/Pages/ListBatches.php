<?php

namespace App\Filament\Resources\Inventory\BatchesResource\Pages;

use App\Filament\Resources\Inventory\BatchesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBatches extends ListRecords
{
    protected static string $resource = BatchesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
