<?php

namespace App\Filament\Resources\Inventory\ProductResource\Pages;

use App\Filament\Resources\Inventory\ProductResource;
use App\Models\Inventory\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // public function getTabs(): array{
    //     return[
    //         'all_stock' => Tab::make('All')
    //         ->modifyQueryUsing(function ($query) {
    //             return $query;
    //         }),
    //         'low_stock' => Tab::make('Low Stock')
    //         ->badge(fn () => $this->getLowStockCount())
    //         ->modifyQueryUsing(function($query) {
    //             return $query->whereBetween('stock', [1, 5]);
    //         }),

    //     'no_stocks' => Tab::make('No Stock')
    //         ->badge(fn () => $this->getNoStockCount())
    //         ->modifyQueryUsing(function($query) {
    //             return $query->where('stock', '=', 0);
    //         }),
    //     'close_to_expire' => Tab::make('Close to Expire')
    //         ->badge(fn () => $this->getCloseToExpireCount())
    //         ->modifyQueryUsing(function($query) {
    //             $futureDate = now()->addDays(30);
    //             return $query->whereNotNull('expiry_date') // Exclude NULL expiry_date
    //                 ->where('expiry_date', '<', $futureDate);
    //         }),

    //     'expired' => Tab::make('Expired')
    //         ->badge(fn () => $this->getExpiredCount())
    //         ->modifyQueryUsing(function($query) {
    //             return $query->whereNotNull('expiry_date') // Exclude NULL expiry_date
    //                 ->where('expiry_date', '<', now());
    //         }),
    //     ];
    // }

    // protected function getLowStockCount(): int
    // {
    //     return Product::whereBetween('stock', [1, 5])->count();
    // }
    
    // protected function getNoStockCount(): int
    // {
    //     return Product::where('stock', '=', 0)->count();
    // }

    // protected function getExpiredCount(): int
    // {
    //     return Product::where('expiry_date', '<', now())->count();
    // }
    // protected function getCloseToExpireCount(): int
    // {
    //     $futureDate = now()->addDays(30);
    //     return Product::where('expiry_date', '<', $futureDate)->count();
    // }
    
}
