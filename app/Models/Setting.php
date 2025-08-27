<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['company_id', 'key', 'value'];

    public static function getTaxRate(?int $companyId = null): float
    {
        $companyId = $companyId ?? auth()->user()->current_company_id ?? config('app.company_id', 1);
        return (float) self::where('company_id', $companyId)->where('key', 'tax_rate')->value('value') ?: 0.1;
    }

    public static function getDefaultDeliveryFee(?int $companyId = null): float
    {
        $companyId = $companyId ?? auth()->user()->current_company_id ?? config('app.company_id', 1);
        return (float) self::where('company_id', $companyId)->where('key', 'default_delivery_fee')->value('value') ?: 0.0;
    }
}