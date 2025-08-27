<?php

namespace App\Models\Sale;

use App\Models\Inventory\Product;
use App\Models\Sale\SaleItemBatch;
use App\Observers\SaleItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(SaleItemObserver::class)]
class SaleItem extends Model
{

    protected $table = 'sale_items';
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
        'cogs',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function saleItemBatches(): HasMany
    {
        return $this->hasMany(SaleItemBatch::class);
    }
}
