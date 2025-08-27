<?php

namespace App\Enums\Category;

use Filament\Support\Contracts\HasLabel;

enum UnitType: string implements HasLabel
{
    case KILOGRAM = 'kg';
    case GRAM = 'g';
    case BOX = 'box';
    case LITER = 'l';
    case MILLILITER = 'ml';
    case PIECE = 'piece';
    case PACK = 'pack';

    public function getLabel(): ?string {
        return $this->name;
    }
}