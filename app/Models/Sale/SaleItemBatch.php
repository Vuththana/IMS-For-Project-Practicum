<?php

namespace App\Models\Sale;

use App\Models\Inventory\Batch; // Ensure this namespace is correct for your Batch model
use App\Models\Sale\SaleItem;    // Correctly reference SaleItem
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemBatch extends Model
{
    protected $table = 'sale_item_batches';

    public $timestamps = false;

    protected $fillable = [
        'sale_item_id',
        'batch_id',
        'quantity',
        'cost_price',
    ];

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}