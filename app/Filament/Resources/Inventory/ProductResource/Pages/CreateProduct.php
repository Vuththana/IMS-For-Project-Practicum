<?php

namespace App\Filament\Resources\Inventory\ProductResource\Pages;

use App\Filament\Resources\Inventory\ProductResource;
use App\Models\Inventory\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
