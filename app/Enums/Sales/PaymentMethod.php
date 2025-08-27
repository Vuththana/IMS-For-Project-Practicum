<?php

namespace App\Enums\Sales;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod:string implements HasLabel
{
    case Cash = 'cash';
    case Card = 'card';
    case Transfer = 'transfer';

    public function getLabel():? string {
        return $this->name;
    }
}
