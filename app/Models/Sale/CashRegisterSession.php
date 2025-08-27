<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Model;

class CashRegisterSession extends Model
{
    protected $fillable = [
        'opened_at',
        'closed_at',
        'opening_balance',
        'closing_balance',
    ];
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];
}