<?php

namespace App\Filament\Resources\Purchase;

use App\Enums\Purchase\PurchaseStatus;
use App\Enums\Sales\PaymentMethod;
use App\Filament\Resources\Purchase\PurchaseResource\Pages;
use App\Filament\Resources\Purchase\PurchaseResource\Pages\Invoice;
use App\Filament\Resources\Purchase\PurchaseResource\RelationManagers;
use App\Models\Inventory\Product;
use App\Models\Partner\Supplier;
use App\Models\Purchase\Purchase;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use App\Models\Setting; // IMPORTANT: Import the Setting model

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('purchaseItems');
    }

    public static function form(Form $form): Form
    {
        $companyId = auth()->user()->current_company_id ?? config('app.company_id', 1);

        $defaultTaxRateFromSettings = (float) Setting::where('company_id', $companyId)->where('key', 'tax_rate')->value('value') * 100;
        $defaultDeliveryFeeFromSettings = (float) Setting::where('company_id', $companyId)->where('key', 'default_delivery_fee')->value('value');

        $initialDefaultTaxRate = $defaultTaxRateFromSettings > 0 ? $defaultTaxRateFromSettings : 0;
        $initialDefaultDeliveryFee = $defaultDeliveryFeeFromSettings > 0 ? $defaultDeliveryFeeFromSettings : 0;

        return $form
            ->schema([
                Section::make('Supplier Information')
                    ->columns(['sm' => 1, 'md' => 2])
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Select Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($supplier = Supplier::find($state)) {
                                    $set('contact_person', $supplier->contact_person);
                                    $set('tax_number', $supplier->tax_number);
                                } else {
                                    $set('contact_person', null);
                                    $set('tax_number', null);
                                }
                                self::runCalculations($get, $set);
                            })
                            ->columnSpanFull()
                            ->createOptionForm([
                                Section::make('New Supplier Details')
                                    ->columns(['sm' => 1, 'md' => 2, 'lg' => 3])
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Supplier Corp.')
                                            ->helperText('Official business name')
                                            ->columnSpanFull(),
                                        TextInput::make('contact_person')
                                            ->placeholder('John Doe')
                                            ->prefixIcon('heroicon-o-user')
                                            ->helperText('Primary contact person')
                                            ->columnSpan(['md' => 1]),
                                        TextInput::make('email')
                                            ->email()
                                            ->placeholder('supplier@example.com')
                                            ->prefixIcon('heroicon-o-at-symbol')
                                            ->columnSpan(['md' => 1]),
                                        PhoneInput::make('phone')
                                            ->label('Phone Number')
                                            ->placeholder('+1 (555) 000-0000')
                                            ->prefixIcon('heroicon-o-phone')
                                            ->columnSpan(['md' => 1]),
                                        Textarea::make('address')
                                            ->placeholder("123 Business St.\nCity, State\nCountry")
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        TextInput::make('tax_number')
                                            ->label('Tax ID')
                                            ->placeholder('XX-XXXXXXX')
                                            ->prefixIcon('heroicon-o-document-text')
                                            ->helperText('VAT or GST number')
                                            ->columnSpan(['md' => 2]),
                                    ]),
                            ]),
                        Fieldset::make('Selected Supplier Details')
                            ->visible(fn (Get $get): bool => (bool) $get('supplier_id'))
                            ->columns(['sm' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('contact_person')
                                    ->dehydrated()
                                    ->disabled()
                                    ->prefixIcon('heroicon-o-user'),
                                TextInput::make('tax_number')
                                    ->dehydrated()
                                    ->disabled()
                                    ->prefixIcon('heroicon-o-document-text')
                                    ->formatStateUsing(fn ($state) => $state
                                        ? 'Verified: ' . $state
                                        : 'Not Provided'),
                            ]),
                    ]),
                Section::make('Purchase Information')
                    ->columns(['sm' => 1, 'md' => 2, 'lg' => 3])
                    ->schema([
                        Grid::make()
                            ->columnSpan(['lg' => 2])
                            ->columns(['sm' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('reference')
                                    ->default(fn () => 'PUR-' . str_pad((Purchase::max('id') + 1), 6, '0', STR_PAD_LEFT))
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(50)
                                    ->columnSpan(['md' => 1]),
                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options(PaymentMethod::class)
                                    ->columnSpan(['md' => 1]),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(PurchaseStatus::class)
                                    ->default('pending')
                                    ->required()
                                    ->columnSpanFull(),
                                DatePicker::make('purchase_date')
                                    ->default(now())
                                    ->required()
                                    ->columnSpan(['md' => 1]),
                                DatePicker::make('expected_delivery_date')
                                    ->required()
                                    ->minDate(fn (Get $get) => $get('purchase_date'))
                                    ->columnSpan(['md' => 1]),
                            ]),
                        Repeater::make('purchaseItems')
                            ->relationship('purchaseItems')
                            ->label('Purchased Items')
                            ->columnSpanFull()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship(
                                        'product',
                                        'name',
                                        fn (Builder $query) => $query->where('purchasable', true)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->allowHtml()
                                    ->getOptionLabelFromRecordUsing(function (Product $product) {
                                        return view('filament.forms.components.product-select-label', [
                                            'name' => $product->name,
                                            'image' => $product->attachments,
                                            'price' => $product->price, // Assuming product price is relevant for cost guidance
                                            'stock' => $product->batches()->sum('remaining_quantity'),
                                                                                        'category_name' => $product->category->name ?? 'N/A',
                                            'subcategory_name' => $product->subcategory->name ?? 'N/A',
                                            'brand_name' => $product->brand->name ?? 'N/A',
                                            'unit_type' => $product->unit_type
                                        ])->render();
                                    })
                                    ->columnSpan(2)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_cost', $product->price); // Assuming product price is initial unit cost
                                            $set('quantity', 1);
                                            self::updatePurchaseItemTotal($get, $set);
                                            self::runCalculations($get, $set, '../../'); // Recalculate grand totals
                                            Notification::make()
                                                ->title('Product Selected')
                                                ->body("Selected {$product->name}")
                                                ->success()
                                                ->send();
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->disabled(fn (Get $get): bool => !$get('product_id'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updatePurchaseItemTotal($get, $set);
                                        self::runCalculations($get, $set, '../../'); // Recalculate grand totals
                                    }),
                                TextInput::make('unit_cost')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->disabled(fn (Get $get): bool => !$get('product_id'))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updatePurchaseItemTotal($get, $set);
                                        self::runCalculations($get, $set, '../../'); // Recalculate grand totals
                                    }),
                                TextInput::make('total')
                                    ->prefix('$')
                                    ->readOnly()
                                    ->numeric()
                                    ->dehydrated(true)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Item')
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible()
                            ->collapsed(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::runCalculations($get, $set, '../../'); // Recalculate grand totals
                            }),
                        Section::make('Financial Summary')
                            ->columnSpan(['lg' => 1, 'md' => 2, 'sm' => 1])
                            ->schema([
                                TextInput::make('subtotal')
                                    ->prefix('$')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated(true)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                                TextInput::make('tax_rate')
                                    ->label('Tax Rate (%)')
                                    ->numeric()
                                    ->default($initialDefaultTaxRate) // Set default from settings
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->dehydrated(true)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::runCalculations($get, $set)),
                                TextInput::make('tax')
                                    ->prefix('$')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated(true)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                                TextInput::make('delivery_fee')
                                    ->prefix('$')
                                    ->numeric()
                                    ->default($initialDefaultDeliveryFee) // Set default from settings
                                    ->dehydrated(true)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::runCalculations($get, $set)),
                                TextInput::make('total_cost')
                                    ->prefix('$')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated(true)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy reference number'),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable()
                    ->label('Date'),
                TextColumn::make('total_cost')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('purchase_items_count')
                    ->label('Items')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('Number of different products in purchase'),
                TextColumn::make('products_summary')
                    ->label('Products')
                    ->formatStateUsing(function ($state, $record) {
                        $items = $record->purchaseItems->take(2);
                        $summary = $items->map(fn($item) => $item->product->name)->join(', ');
                        return $record->purchaseItems->count() > 2 ? $summary . ' +' . ($record->purchaseItems->count() - 2) . ' more' : $summary;
                    })
                    ->tooltip(fn ($record) => $record->purchaseItems->map(
                        fn($item) => $item->product->name . ' (' . $item->quantity . 'x)'
                    )->join(PHP_EOL))
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('tax') // Display tax in the table
                    ->money('USD')
                    ->icon('heroicon-o-receipt-percent')
                    ->label('Tax'),
                TextColumn::make('delivery_fee') // Display delivery fee in the table
                    ->money('USD')
                    ->icon('heroicon-o-truck')
                    ->label('Delivery Fee'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->created_at->format('M d, Y h:i A')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make("view_invoice")
                    ->label("View Invoice")
                    ->icon("heroicon-o-document")
                    ->url(fn($record): mixed => Invoice::getUrl(['record' => $record->id]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('mark_as_received')
                    ->label('Mark as Received')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => is_null($record->received_date) && $record->status !== 'received')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'received_date' => now(),
                            'status' => 'received',
                        ]);
                        $record->finalizePurchase(); // Make sure this method handles inventory updates
                        Notification::make()
                            ->title('Purchase marked as received!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Updates the total for a single purchase item.
     */
    protected static function updatePurchaseItemTotal(Get $get, Set $set): void
    {
        $quantity = (float) $get('quantity');
        $unitCost = (float) $get('unit_cost');
        $total = round($quantity * $unitCost, 2);
        $set('total', $total);
    }

    /**
     * Runs all grand total calculations for the purchase.
     */
    protected static function runCalculations(Get $get, Set $set, string $parentPath = ''): void
    {
        $purchaseItems = $get($parentPath . 'purchaseItems');
        $subtotal = 0;

        if (is_array($purchaseItems)) {
            foreach ($purchaseItems as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitCost = (float) ($item['item_cost'] ?? $item['unit_cost'] ?? 0); // Use item_cost first if available, then unit_cost
                $subtotal += $quantity * $unitCost;
            }
        }

        // Get values directly from the form fields, which might have default values
        $taxRate = (float) ($get($parentPath . 'tax_rate') ?? 0); // Use the value from the form's tax_rate field
        $deliveryFee = (float) ($get($parentPath . 'delivery_fee') ?? 0);

        // Calculate tax based on the subtotal and the form's tax_rate field
        $calculatedTax = round($subtotal * ($taxRate / 100), 2);

        $totalCost = round($subtotal + $calculatedTax + $deliveryFee, 2);

        // Set form fields
        $set($parentPath . 'subtotal', $subtotal);
        $set($parentPath . 'tax', $calculatedTax); // Always update the 'tax' field with the calculated value
        $set($parentPath . 'total_cost', $totalCost);
    }

    /**
     * Validates inputs before running grand total calculations.
     */
    protected static function validateCalculationInputs(Get $get): bool
    {
        $purchaseItems = $get('purchaseItems');

        if (empty($purchaseItems)) {
            Notification::make()
                ->title('No Items')
                ->body('Please add at least one item to calculate totals.')
                ->warning()
                ->send();
            return false;
        }

        foreach ($purchaseItems as $item) {
            if (empty($item['product_id'])) { // Changed from !isset to empty for better check
                Notification::make()
                    ->title('Incomplete Item Details')
                    ->body('Ensure all purchase items have a product selected.')
                    ->warning()
                    ->send();
                return false;
            }
            
            if (!is_numeric($item['quantity']) || (float)$item['quantity'] <= 0) { // Specific check for quantity
                Notification::make()
                    ->title("Invalid Quantity for Product ID: {$item['product_id']}")
                    ->body('Quantity must be a positive number for all items.')
                    ->warning()
                    ->send();
                return false;
            }
            
            if (!is_numeric($item['unit_cost']) || (float)$item['unit_cost'] < 0) { // Specific check for unit_cost
                Notification::make()
                    ->title("Invalid Unit Cost for Product ID: {$item['product_id']}")
                    ->body('Unit Cost must be a non-negative number for all items.')
                    ->warning()
                    ->send();
                return false;
            }
        }

        // Validate financial fields
        if (!is_numeric($get('tax_rate')) || (float)$get('tax_rate') < 0 || (float)$get('tax_rate') > 100) {
            Notification::make()
                ->title('Invalid Tax Rate')
                ->body('Tax Rate must be between 0 and 100.')
                ->warning()
                ->send();
            return false;
        }

        if (!is_numeric($get('delivery_fee')) || (float)$get('delivery_fee') < 0) {
            Notification::make()
                ->title('Invalid Delivery Fee')
                ->body('Delivery Fee must be a non-negative number.')
                ->warning()
                ->send();
            return false;
        }

        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
            'invoice' => Invoice::route('/{record}/invoice'),
        ];
    }
}