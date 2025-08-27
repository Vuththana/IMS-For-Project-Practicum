<?php

namespace App\Filament\Widgets;

use App\Models\Sale\SaleItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TopSellingProducts extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $topSellingProducts = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return "{$item->product->name} ({$item->total_sold})";
            })->implode(', ');
        return [
            Stat::make('Top 5 Products', $topSellingProducts)
                ->icon('heroicon-o-fire')
                ->description('Most sold by quantity')
                ->color('success'),
        ];
    }
}
