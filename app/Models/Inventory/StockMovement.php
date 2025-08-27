<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\InventoryAdjustmentType;
use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'type',
        'quantity',
        'cost_price',
        'total_cost',
        'note',
        'moved_at',
    ];
    public function getDirectionAttribute(): string
    {
        return match($this->type) {
            'purchase', 'purchase_update', 'return_sale', 'add', 'initial_stock_correction' => 'in',
            'sale', 'sale_update', 'return_purchase', 'remove', 'damaged', 'theft', 'spoilage' => 'out',
            default => 'neutral',
        };
    }

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    protected $casts = [
        'moved_at' => 'datetime',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
