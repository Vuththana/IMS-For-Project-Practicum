<?php

namespace App\Enums\Purchase;

use Filament\Support\Contracts\HasLabel;

enum PurchaseStatus:string implements HasLabel
{
    case Pending = 'pending';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function getLabel():? string {
        return $this->name;
    }
}