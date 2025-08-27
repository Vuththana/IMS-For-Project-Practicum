<?php

namespace App\Filament\Resources\Inventory\BatchesResource\Pages;

use App\Filament\Resources\Inventory\BatchesResource;
use App\Models\Inventory\Batch;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBatches extends CreateRecord
{
    protected static string $resource = BatchesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['remaining_quantity'] = $data['quantity'];
        return $data;
    }
    

}
