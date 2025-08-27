<?php

namespace App\Observers\Inventory;

use App\Models\Inventory\InventoryAdjustment;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class InventoryAdjustmentObserver
{

    public function created(InventoryAdjustment $inventoryAdjustment): void
    {
        
        $recipient = auth()->user();

        Notification::make()
                ->title('Stock Logs Updated')
                ->sendToDatabase($recipient);
    }

    public function creating(InventoryAdjustment $inventoryAdjustment): void
    {
        if (Auth::check() && empty($inventoryAdjustment->company_id)) {
            $inventoryAdjustment->company_id = Auth::user()->current_company_id;
        }
    }
}
