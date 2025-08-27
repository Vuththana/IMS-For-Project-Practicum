<?php

namespace App\Models;

use App\Models\Inventory\Batch;
use App\Models\Purchase\PurchaseItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItemBatch extends Model
{
    protected $table = 'purchase_item_batches';

    public $timestamps = false;

    protected $fillable = [
        'purchase_item_id',
        'batch_id',
        'quantity',
        'cost_price',
    ];

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
