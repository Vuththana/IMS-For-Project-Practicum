<?php

namespace App\Models;

use App\Models\Inventory\Product;
use App\Models\Sale\Sale;
use Illuminate\Database\Eloquent\Model;

class SaleInvoiceRecord extends Model
{
    protected $fillable = [
        'sale_id',
        'data',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
