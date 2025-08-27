<?php

namespace App\Enums\Sale;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SaleStatus: string implements HasLabel, HasColor, HasIcon
{
    case DRAFT = 'draft';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';
    case REFUNDED = 'refunded';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::COMPLETED => __('Completed'),
            self::CANCELLED => __('Cancelled'),
            self::RETURNED => __('Returned'),
            self::REFUNDED => __('Refunded'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
            self::RETURNED => 'warning',
            self::REFUNDED => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document-text',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::RETURNED => 'heroicon-o-arrow-uturn-left',
            self::REFUNDED => 'heroicon-o-receipt-refund',
        };
    }

    // Optional: Method to get all values for select options
    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->getLabel();
        }
        return $array;
    }
}