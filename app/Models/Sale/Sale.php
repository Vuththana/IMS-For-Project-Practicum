<?php

namespace App\Models\Sale;

use App\Enums\Delivery\DeliveryStatus;
use App\Enums\Sale\SaleStatus; // Make sure this Enum is correctly namespaced
use App\Enums\Sales\PaymentMethod;
use App\Models\Company;
use App\Models\Sale\CashRegisterSession;
use App\Models\Inventory\Batch; // Assuming this is your Batch model
use App\Models\Inventory\Product; // For type hinting
use App\Models\Inventory\StockMovement;
use App\Models\Partner\Customer;
use App\Models\Partner\Deliverer;
use App\Models\Scopes\CompanyScope;
use App\Observers\Sale\SaleCompanyCreate;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
#[ObservedBy(classes: SaleCompanyCreate::class)]
class Sale extends Model
{
    protected $table = 'sales';
    protected $fillable = [
        'company_id',
        'deliverer_id',
        'customer_id',
        'invoice_number',
        'sale_date',
        'payment_method',
        'status',
        'delivery_status',
        'total_amount',
        'delivery_fee',
        'discount',
        'tax',
        'subtotal',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status' => SaleStatus::class,
        'delivery_status' => DeliveryStatus::class,
        'sale_date' => 'date',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
    public function finalizeSale(string $type = 'sale'): void
    {
        DB::transaction(function () use ($type) {
            foreach ($this->saleItems()->with('product')->get() as $item) {
                $product = $item->product;
                $quantityToDeduct = $item->quantity;
                $totalCogsForItem = 0;
    
                if (!$product) {
                    throw new \Exception("Product not found for sale item ID: {$item->id}.");
                }
    
                $batches = Batch::where('product_id', $product->id)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
    
                foreach ($batches as $batch) {
                    if ($quantityToDeduct <= 0) {
                        break;
                    }
    
                    $deductQty = min($quantityToDeduct, $batch->remaining_quantity);
    
                    if ($deductQty <= 0) {
                        continue;
                    }
    
                    $batch->decrement('remaining_quantity', $deductQty);
                    $totalCogsForItem += $deductQty * ($batch->cost_price ?? 0);
    
                    SaleItemBatch::create([
                        'sale_item_id' => $item->id,
                        'batch_id'     => $batch->id,
                        'quantity'     => $deductQty,
                        'cost_price'   => $batch->cost_price ?? 0,
                    ]);
    
                    StockMovement::create([
                        'company_id' => Auth::user()->current_company_id,
                        'product_id' => $product->id,
                        'batch_id'   => $batch->id,
                        'type'       => $type,
                        'quantity'   => $deductQty,
                        'note'       => 'Sold via invoice #' . $this->invoice_number,
                        'moved_at'   => $this->sale_date ?? now(),
                    ]);
    
                    $quantityToDeduct -= $deductQty;
                }
    
                if ($quantityToDeduct > 0) {
                    throw new \Exception("Insufficient stock for product '{$product->name}' (ID: {$product->id}). Required: {$item->quantity}, available: " . ($item->quantity - $quantityToDeduct));
                }
                $totalCogsForItem += $deductQty * $batch->cost_price;
                $item->cogs = $totalCogsForItem;
                $item->saveQuietly();
            }
        });
    }
    
    public function revertStock(string $revertType = 'sale_reverted'): void
    {
        DB::transaction(function () use ($revertType) {
            foreach ($this->saleItems()->with('saleItemBatches.batch.product')->get() as $item) {
                $product = $item->product;
    
                if (!$product) {
                    $firstBatch = $item->saleItemBatches->first();
                    if ($firstBatch && $firstBatch->batch) {
                        $product = $firstBatch->batch->product;
                    }
                }
    
                if (!$product || !$product->id) {
                    \Log::warning("Cannot revert stock for sale item ID {$item->id}: Product missing.");
                    continue;
                }
    
                $totalCogsForItem = 0;
    
                foreach ($item->saleItemBatches as $saleItemBatch) {
                    if (!$saleItemBatch->quantity) {
                        continue;
                    }
    
                    $batch = $saleItemBatch->batch;
    
                    if ($batch) {
                        $batch->increment('remaining_quantity', $saleItemBatch->quantity);
                    }
    
                    // Add to COGS (optional: customize based on your model logic)
                    $totalCogsForItem += $saleItemBatch->quantity * ($batch?->cost_price ?? 0);
    
                    StockMovement::create([
                        'product_id' => $product->id,
                        'batch_id'   => $saleItemBatch->batch_id,
                        'type'       => $revertType,
                        'quantity'   => $saleItemBatch->quantity,
                        'note'       => 'Stock reverted for invoice #' . $this->invoice_number . ' due to ' . $revertType,
                        'moved_at'   => now(),
                    ]);
    
                    $saleItemBatch->delete();
                }
    
                $item->cogs = $totalCogsForItem;
                $item->saveQuietly();
            }
        });
    }
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(Deliverer::class, 'deliverer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function cashRegisterSession(): BelongsTo
    {
        return $this->belongsTo(CashRegisterSession::class);
    }
}