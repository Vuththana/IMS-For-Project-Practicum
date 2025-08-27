<?php

namespace App\Enums\Inventory;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InventoryAdjustmentType: string implements HasLabel, HasColor
{
    case Add = 'add';
    case Remove = 'remove';
    case InitialStockCorrection = 'initial_stock_correction';
    case ReturnSale = 'return_sale';
    case ReturnPurchase = 'return_purchase';
    case Damage = 'damage';
    case Spoilage = 'spoilage';
    case Theft = 'theft';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Add => 'Stock Added',
            self::Remove => 'Stock Removed',
            self::InitialStockCorrection => 'Initial Stock Correction',
            self::ReturnSale => 'Customer Return (Sale)',
            self::ReturnPurchase => 'Supplier Return (Purchase)',
            self::Damage => 'Damage',
            self::Spoilage => 'Spoilage',
            self::Theft => 'Theft',
            self::Other => 'Other',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Add, self::InitialStockCorrection, self::ReturnSale, self::ReturnPurchase => Color::Green,
            self::Remove, self::Damage, self::Spoilage, self::Theft => Color::Red,
            self::Other => Color::Blue,
        };
    }

    // NEW: Helper to check if type increases stock
    public function isAddType(): bool
    {
        return in_array($this, [
            self::Add,
            self::InitialStockCorrection,
            self::ReturnSale,
            self::ReturnPurchase,
        ]);
    }

    // NEW: Helper to check if type decreases stock
    public function isRemoveType(): bool
    {
        return in_array($this, [
            self::Remove,
            self::Damage,
            self::Spoilage,
            self::Theft,
            self::Other,
        ]);
    }
}