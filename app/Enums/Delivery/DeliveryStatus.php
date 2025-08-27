<?php

namespace App\Enums\Delivery;

use Filament\Support\Contracts\HasLabel;

enum DeliveryStatus:string implements HasLabel
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function getLabel():? string {
        return $this->name;
    }
}