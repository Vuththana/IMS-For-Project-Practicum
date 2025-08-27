<?php

namespace App\Filament\Resources\Inventory;

use App\Filament\Resources\Inventory\InventoryAdjustmentResource\Pages;
use App\Models\Inventory\Batch;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Purchase\Purchase;
use App\Models\Sale\Sale;
use App\Models\Inventory\Product;
use App\Enums\Inventory\InventoryAdjustmentType;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;

class InventoryAdjustmentResource extends Resource
{
    protected static ?string $model = InventoryAdjustment::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $modelLabel = 'Stock Adjustment';
    protected static ?string $pluralModelLabel = 'Stock Adjustments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Stock Adjustment Details')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->allowHtml()
                                    ->getOptionLabelFromRecordUsing(function (Product $product) {
                                        return view('filament.forms.components.inventory-adjustment.product-select-label', [
                                            'name' => $product->name,
                                            'image' => $product->attachments,
                                            'price' => $product->price,
                                            'category_name' => $product->category->name ?? 'N/A',
                                            'subcategory_name' => $product->subcategory->name ?? 'N/A',
                                            'brand_name' => $product->brand->name ?? 'N/A',
                                            'unit_type' => $product->unit_type
                                        ])->render();
                                    })
                            ->afterStateUpdated(function (Set $set) {
                                $set('batch_id', null);
                            })
                            ->disabled(fn (?InventoryAdjustment $record) => $record !== null),
                        Select::make('batch_id')
                            ->label('Batch')
                            ->options(function (Get $get) {
                                $productId = $get('product_id');
                                if (!$productId) return [];
                        
                                return Batch::where('product_id', $productId)
                                    ->get()
                                    ->pluck('batch_number', 'id')
                                    ->map(fn ($batchNumber, $id) => "{$batchNumber} (" . Batch::find($id)?->remaining_quantity . " units)")
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->disabled(fn (?InventoryAdjustment $record) => $record !== null),
                        Select::make('type')
                            ->options(InventoryAdjustmentType::class)
                            ->required()
                            ->reactive()
                            ->disabled(fn (?InventoryAdjustment $record) => $record !== null),
                        TextInput::make('adjusted_quantity')
                            ->numeric()
                            ->suffix('units')
                            ->required()
                            ->minValue(function (Get $get) {
                                return in_array($get('type'), [
                                    InventoryAdjustmentType::Add->value,
                                    InventoryAdjustmentType::InitialStockCorrection->value,
                                    InventoryAdjustmentType::ReturnSale->value,
                                    InventoryAdjustmentType::ReturnPurchase->value,
                                ]) ? 1 : null;
                            })
                            ->maxValue(function (Get $get, ?InventoryAdjustment $record) {
                                $type = $get('type');
                                $batchId = $get('batch_id');
                                if (in_array($type, [
                                    InventoryAdjustmentType::Remove->value,
                                    InventoryAdjustmentType::Damage->value,
                                    InventoryAdjustmentType::Spoilage->value,
                                    InventoryAdjustmentType::Theft->value,
                                    InventoryAdjustmentType::Other->value,
                                ]) && $batchId) {
                                    $batch = Batch::find($batchId);
                                    if (!$batch) return null;
                                    $currentAdjustedQtyOnRecord = $record ? $record->adjusted_quantity : 0;
                                    return $batch->remaining_quantity + $currentAdjustedQtyOnRecord;
                                }
                                return null;
                            })
                            ->hint(function (Get $get, ?InventoryAdjustment $record) {
                                $type = $get('type');
                                $batchId = $get('batch_id');
                                if (in_array($type, [
                                    InventoryAdjustmentType::Remove->value,
                                    InventoryAdjustmentType::Damage->value,
                                    InventoryAdjustmentType::Spoilage->value,
                                    InventoryAdjustmentType::Theft->value,
                                    InventoryAdjustmentType::Other->value,
                                ]) && $batchId) {
                                    $batch = Batch::find($batchId);
                                    $currentAdjustedQtyOnRecord = $record ? $record->adjusted_quantity : 0;
                                    $maxAvailable = ($batch ? $batch->remaining_quantity : 0) + $currentAdjustedQtyOnRecord;
                                    return "Cannot exceed batch stock ($maxAvailable units available for removal).";
                                }
                                return null;
                            })
                            ->rules([
                                function (Get $get, ?InventoryAdjustment $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        $type = $get('type');
                                        $batchId = $get('batch_id');
                                        $relatedPurchaseId = $get('related_purchase_id');

                                        if ($type === InventoryAdjustmentType::ReturnPurchase->value && $batchId && $relatedPurchaseId) {
                                            $batch = Batch::find($batchId);
                                            $purchase = Purchase::find($relatedPurchaseId);

                                            if (!$batch || !$purchase) {
                                                $fail('Selected batch or purchase not found.');
                                                return;
                                            }

                                            $purchaseItem = $purchase->purchaseItems()
                                                ->where('product_id', $batch->product_id)
                                                ->first();

                                            if (!$purchaseItem) {
                                                $fail('Product not found in selected purchase.');
                                                return;
                                            }

                                            $alreadyReturned = InventoryAdjustment::where('related_purchase_id', $relatedPurchaseId)
                                                ->where('batch_id', $batchId)
                                                ->where('type', InventoryAdjustmentType::ReturnPurchase)
                                                ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                                ->sum('adjusted_quantity');

                                            $maxReturnable = $purchaseItem->quantity - $alreadyReturned;

                                            if ($value > $maxReturnable) {
                                                $fail("Only {$maxReturnable} units of this batch can be returned from this purchase.");
                                            }
                                        }
                                    };
                                }
                            ]),
                        Textarea::make('reason')
                            ->label('Reason')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('related_sale_id')
                            ->label('Related Sale')
                            ->relationship('sale', 'id', fn (Builder $query) => $query->where('status', 'completed'))
                            ->getOptionLabelFromRecordUsing(fn (Sale $record) => "Sale #{$record->id} - {$record->created_at->format('M d, Y')}")
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn (Get $get) => $get('type') === InventoryAdjustmentType::ReturnSale->value)
                            ->required(fn (Get $get) => $get('type') === InventoryAdjustmentType::ReturnSale->value)
                            ->columnSpanFull(),
                        Select::make('related_purchase_id')
                            ->label('Related Purchase')
                            ->relationship('purchase', 'reference', fn (Builder $query) => $query->where('status', 'received'))
                            ->getOptionLabelFromRecordUsing(fn (Purchase $record) => "Purchase {$record->reference} - {$record->supplier->name}")
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn (Get $get) => $get('type') === InventoryAdjustmentType::ReturnPurchase->value)
                            ->required(fn (Get $get) => $get('type') === InventoryAdjustmentType::ReturnPurchase->value)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->tooltip(fn ($record) => $record->created_at->format('Y-m-d H:i:s')),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch.batch_number')
                    ->label('Batch Number')
                    ->toggleable()
                    ->searchable()
                    ->placeholder('N/A'),
                TextColumn::make('type')
                    ->label('Adjustment Type')
                    ->badge(),
                TextColumn::make('adjusted_quantity')
                    ->label('Qty Change')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn (InventoryAdjustment $record): string => $record->type->isAddType() ? 'success' : 'danger'),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->reason;
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('quantity_change')
                    ->form([
                        TextInput::make('min_qty')
                            ->numeric()
                            ->placeholder('Min Qty Change'),
                        TextInput::make('max_qty')
                            ->numeric()
                            ->placeholder('Max Qty Change'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_qty'], fn (Builder $query, $qty) => $query->where('adjusted_quantity', '>=', $qty))
                            ->when($data['max_qty'], fn (Builder $query, $qty) => $query->where('adjusted_quantity', '<=', $qty));
                    }),
                DateRangeFilter::make('created_at'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryAdjustments::route('/'),
            'create' => Pages\CreateInventoryAdjustment::route('/create'),
            'edit' => Pages\EditInventoryAdjustment::route('/{record}/edit'),
        ];
    }
}