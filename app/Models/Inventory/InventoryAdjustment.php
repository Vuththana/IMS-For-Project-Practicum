<?php

namespace App\Models\Inventory;

use App\Enums\Inventory\InventoryAdjustmentType;
use App\Models\Purchase\Purchase;
use App\Models\Sale\Sale;
use App\Models\Partner\Customer;
use App\Models\Scopes\CompanyScope;
use App\Observers\Inventory\InventoryAdjustmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Exception;

#[ObservedBy([InventoryAdjustmentObserver::class])]
class InventoryAdjustment extends Model
{
    protected $table = 'inventory_adjustments';

    protected $fillable = [
        'company_id',
        'product_id',
        'batch_id',
        'type',
        'adjusted_quantity',
        'reason',
        'related_sale_id',
        'related_purchase_id',
    ];

    protected $casts = [
        'type' => InventoryAdjustmentType::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    // Relationships
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'related_sale_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'related_purchase_id');
    }

    /**
     * Finalize the inventory adjustment by updating batch quantity and logging stock movement.
     *
     * @param string $type
     * @return void
     * @throws \Exception
     */
    public function finalizeAdjustment(): void
    {
        DB::transaction(function () {
            $batch = $this->batch;
            if (!$batch) {
                Notification::make()
                    ->title('Batch not found for adjustment.')
                    ->danger()
                    ->send();
                throw new \Exception('Batch not found.');
            }
    
            $adjustmentType = $this->type;
            $quantity = $this->adjusted_quantity;
    
            if ($adjustmentType->isAddType()) {
                $batch->increment('remaining_quantity', $quantity);
    
                StockMovement::create([
                    'company_id' => Auth::user()->current_company_id,
                    'product_id' => $this->product_id,
                    'batch_id' => $this->batch_id,
                    'type' => $adjustmentType->value, // ← uses enum value like 'add', 'return_sale'
                    'quantity' => $quantity,
                    'note' => "Stock added via adjustment (Type: {$adjustmentType->getLabel()})",
                    'moved_at' => now(),
                ]);
            } elseif ($adjustmentType->isRemoveType()) {
                if ($batch->remaining_quantity < $quantity) {
                    Notification::make()
                        ->title('Insufficient batch stock for removal.')
                        ->body("Attempted to remove {$quantity} but only {$batch->remaining_quantity} available.")
                        ->danger()
                        ->send();
                    throw new \Exception('Insufficient batch stock for removal.');
                }
    
                $batch->decrement('remaining_quantity', $quantity);
    
                StockMovement::create([
                    'company_id' => Auth::user()->current_company_id,
                    'product_id' => $this->product_id,
                    'batch_id' => $this->batch_id,
                    'type' => $adjustmentType->value, // ← same here
                    'quantity' => $quantity,
                    'note' => "Stock removed via adjustment (Type: {$adjustmentType->getLabel()})",
                    'moved_at' => now(),
                ]);
            }
    
            Notification::make()
                ->title('Stock adjustment applied successfully!')
                ->success()
                ->send();
        });
    }

    public function revertStock(): void
    {
        DB::transaction(function () {
            $originalBatchId = $this->getOriginal('batch_id', $this->batch_id);
            $batch = Batch::find($originalBatchId);

            if (!$batch) {
                throw new Exception('Original batch with ID ' . $originalBatchId . ' not found for stock reversion.');
            }

            $originalType = $this->getOriginal('type', $this->type);
            $adjustmentType = $originalType instanceof InventoryAdjustmentType
                ? $originalType
                : InventoryAdjustmentType::from($originalType);
            
            $quantity = $this->getOriginal('adjusted_quantity', $this->adjusted_quantity);

            if ($adjustmentType->isAddType()) {
                if ($batch->remaining_quantity < $quantity) {
                     Notification::make()
                        ->title('Insufficient stock for reversion.')
                        ->body("Cannot revert ADD of {$quantity}. Only {$batch->remaining_quantity} available in Batch #{$batch->id}.")
                        ->danger()
                        ->send();
                    throw new Exception('Insufficient stock to revert adjustment.');
                }
                $batch->decrement('remaining_quantity', $quantity);
                 StockMovement::create([
                    'company_id' => $this->company_id,
                    'product_id' => $this->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'revert_add', // Custom type for clarity
                    'quantity' => $quantity,
                    'note' => "Reverted previous stock addition from adjustment #{$this->id}.",
                    'moved_at' => now(),
                ]);

            // REVERSE THE LOGIC: If it was a REMOVE, we now ADD.
            } elseif ($adjustmentType->isRemoveType()) {
                $batch->increment('remaining_quantity', $quantity);
                StockMovement::create([
                    'company_id' => $this->company_id,
                    'product_id' => $this->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'revert_remove', // Custom type for clarity
                    'quantity' => $quantity,
                    'note' => "Reverted previous stock removal from adjustment #{$this->id}.",
                    'moved_at' => now(),
                ]);
            }
        });
    }
}