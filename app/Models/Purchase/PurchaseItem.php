<?php

namespace App\Models\Purchase;

use App\Models\Inventory\Product;
use App\Models\PurchaseItemBatch;
use App\Observers\PurchaseItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(PurchaseItemObserver::class)]
class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total'
    ];
    protected static function booted()
    {
        static::deleted(function ($item) {
            $item->product->decrement('stock', $item->quantity);
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
    public function purchaseItemBatches(): HasMany
    {
        return $this->hasMany(PurchaseItemBatch::class);
    }
}
