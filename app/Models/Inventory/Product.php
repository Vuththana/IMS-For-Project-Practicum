<?php

namespace App\Models\Inventory;

use App\Models\Company;
use App\Models\Sale\SaleItem;
use App\Models\Scopes\CompanyScope;
use App\Observers\Inventory\ProductCompanyCreate;
use App\Observers\Inventory\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
#[ObservedBy(ProductCompanyCreate::class)]
class Product extends Model
{
    protected $fillable = [
        'company_id',
        'attachments',
        'name',
        'description',
        'price',
        'sku',
        'category_id',
        'subcategory_id',
        'brand_id',
        'purchasable',
        'sellable',
        'barcode',
        'unit_type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sellable' => 'boolean',
        'purchasable' => 'boolean',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function companies(): HasMany {
        return $this->hasMany(Company::class);
    }
    public function categories(): HasMany {
        return $this->hasMany(Category::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function getTotalRemainingStockAttribute(): int
    {
        $stock = $this->batches()
            ->where('remaining_quantity', '>', 0)
            ->where(function($query) { // Filter for non-expired batches
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            })
            ->sum('remaining_quantity');
        return (int) $stock;
    }


}
