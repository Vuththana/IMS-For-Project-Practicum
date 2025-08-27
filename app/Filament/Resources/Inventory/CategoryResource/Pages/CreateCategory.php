<?php

namespace App\Filament\Resources\Inventory\CategoryResource\Pages;

use App\Filament\Resources\Inventory\CategoryResource;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
