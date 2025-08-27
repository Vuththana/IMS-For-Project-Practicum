<?php

namespace App\Models\Purchase;

use App\Enums\Purchase\PurchaseStatus;
use App\Enums\Sales\PaymentMethod;
use App\Models\Company;
use App\Models\Inventory\Batch;
use App\Models\Inventory\StockMovement;
use App\Models\Partner\Supplier;
use App\Models\PurchaseItemBatch;
use App\Models\Scopes\CompanyScope;
use App\Observers\Purchase\PurchaseCompanyCreate;
use App\Observers\PurchaseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
#[ObservedBy(classes: PurchaseCompanyCreate::class)]
class Purchase extends Model
{
    protected $fillable = [
        'company_id',
        'supplier_id',
        'reference',
        'invoice_number',
        'purchase_date',
        'expected_delivery_date',
        'subtotal',
        'discount',
        'tax',
        'delivery_fee',
        'total_cost',
        'received_date',
        'status',
        'is_paid',
        'payment_method',
    ];
    protected $cast = ['status' => PurchaseStatus::class, 'is_paid' => 'boolean', 'payment_method' => PaymentMethod::class];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public static function generateBatchNumber(): string
{
    $prefix = 'BAT';
    $timestamp = now()->format('Ymd');
    $random = strtoupper(\Illuminate\Support\Str::random(4));
    return "{$prefix}-{$timestamp}-{$random}";
}
    public function finalizePurchase(string $type = 'purchase'): void
    {
        DB::transaction(function () use ($type) {
            foreach ($this->purchaseItems()->with('product')->get() as $item) {
                $product = $item->product;
    
                if (!$product) {
                    throw new \Exception("Product not found for purchase item ID: {$item->id}.");
                }

                $batchNumber = self::generateBatchNumber();
                
                $batch = Batch::create([
                    'company_id' => Auth::user()->id,
                    'product_id' => $product->id,
                    'batch_number' => $batchNumber,
                    'cost_price' => $item->unit_cost,
                    'quantity' => $item->quantity,
                    'remaining_quantity' => $item->quantity,
                    'expiry_date' => $item->expiry_date ?? null,
                ]);
    
                PurchaseItemBatch::create([
                    'purchase_item_id' => $item->id,
                    'batch_id' => $batch->id,
                    'quantity' => $item->quantity,
                    'cost_price' => $item->unit_cost,
                ]);
    
                StockMovement::create([
                    'company_id' => Auth::user()->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'type' => $type,
                    'quantity' => $item->quantity,
                    'note' => 'Purchased via reference #' . $this->reference,
                    'moved_at' => $this->purchase_date ?? now(),
                ]);
    
                $item->saveQuietly();
            }
        });
    }
    
    public function revertStock(string $revertType = 'purchase_reverted'): void
    {
        DB::transaction(function () use ($revertType) {
            foreach ($this->purchaseItems()->with('purchaseItemBatches.batch.product')->get() as $item) {
                $product = $item->product;
    
    
                foreach ($item->purchaseItemBatches as $purchaseItemBatch) {
                    $batch = $purchaseItemBatch->batch;
    
                    if ($batch) {
                        $batch->decrement('remaining_quantity', $purchaseItemBatch->quantity);
                    }
    
                    StockMovement::create([
                        'product_id' => $product->id,
                        'batch_id' => $purchaseItemBatch->batch_id,
                        'type' => $revertType,
                        'quantity' => -$purchaseItemBatch->quantity,
                        'note' => 'Stock reverted for reference #' . $this->reference,
                        'moved_at' => now(),
                    ]);
    
                    $purchaseItemBatch->delete();
                }
    
                $item->cogs = null;
                $item->saveQuietly();
            }
        });
    }
    
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo {
        return $this->belongsTo(Supplier::class);
    }
}
