<?php

namespace App\Filament\Resources\Partner\DelivererResource\Pages;

use App\Filament\Resources\Partner\DelivererResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliverer extends EditRecord
{
    protected static string $resource = DelivererResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
