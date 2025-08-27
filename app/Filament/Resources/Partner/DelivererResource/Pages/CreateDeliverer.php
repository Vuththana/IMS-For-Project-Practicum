<?php

namespace App\Filament\Resources\Partner\DelivererResource\Pages;

use App\Filament\Resources\Partner\DelivererResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliverer extends CreateRecord
{
    protected static string $resource = DelivererResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
