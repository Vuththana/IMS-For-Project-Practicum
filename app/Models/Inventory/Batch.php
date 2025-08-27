<?php

namespace App\Models\Inventory;

use App\Models\Scopes\CompanyScope;
use App\Observers\Inventory\BatchCompanyCreate;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ObservedBy(BatchCompanyCreate::class)]
class Batch extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'batch_number',
        'cost_price',
        'quantity',
        'expiry_date',
        'remaining_quantity',
    ];
        protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
