<?php

namespace App\Filament\Resources\Sale;

use App\Filament\Resources\Partner\DelivererResource;
use App\Filament\Resources\Sale\SalesResource\Pages;
use App\Models\Inventory\Product;
use App\Models\Sale\Sale;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use App\Enums\Sales\PaymentMethod;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Enums\Delivery\DeliveryStatus;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Filament\Resources\Sale\SalesResource\Pages\Invoice;
use App\Models\Inventory\Batch;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class SalesResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $companyId = auth()->user()->current_company_id ?? config('app.company_id', 1);

        $defaultTaxRatePercentage = (float) Setting::where('company_id', $companyId)->where('key', 'tax_rate')->value('value') * 100;
        $defaultDeliveryFee = (float) Setting::where('company_id', $companyId)->where('key', 'default_delivery_fee')->value('value');
        $initialDefaultTaxRate = $defaultTaxRatePercentage > 0 ? $defaultTaxRatePercentage : 0;
        $defaultDeliveryFee = $defaultDeliveryFee > 0 ? $defaultDeliveryFee : 0;

        return $form
            ->schema([
                Section::make('Sale Information')
                    ->columns(['md' => 2, 'lg' => 3])
                    ->schema([
                        Grid::make()
                            ->columnSpan(['lg' => 2])
                            ->columns(['md' => 2])
                            ->schema([
                                Select::make('deliverer_id')
                                    ->relationship('deliverer', 'name')
                                    ->label('Delivery Partner')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required()
                                    ->createOptionForm([
                                        Section::make('Deliverer Information')
                                            ->columns(['md' => 2, 'lg' => 3])
                                            ->schema([
                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 1])
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->placeholder('John Doe')
                                                            ->columnSpanFull(),

                                                        Select::make('type')
                                                            ->options([
                                                                'personal' => 'Personal',
                                                                'institution' => 'Institution',
                                                            ])
                                                            ->required()
                                                            ->native(false)
                                                            ->placeholder('Select account type')
                                                            ->helperText('Choose deliverer category'),
                                                    ]),

                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 2])
                                                    ->columns(['md' => 2])
                                                    ->schema([
                                                        PhoneInput::make('phone_number'),
                                                        TextInput::make('email')
                                                            ->email()
                                                            ->placeholder('john@example.com')
                                                            ->prefixIcon('heroicon-o-envelope'),

                                                        TextInput::make('address')
                                                            ->columnSpan(['md' => 2])
                                                            ->placeholder('123 Main St, City, Country')
                                                            ->prefixIcon('heroicon-o-map-pin')
                                                            ->helperText('Full delivery address'),
                                                    ]),
                                            ]),
                                    ])
                                    ->editOptionForm([
                                        Section::make('Deliverer Information')
                                            ->columns(['md' => 2, 'lg' => 3])
                                            ->schema([
                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 1])
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->placeholder('John Doe')
                                                            ->columnSpanFull(),

                                                        Select::make('type')
                                                            ->options([
                                                                'personal' => 'Personal',
                                                                'institution' => 'Institution',
                                                            ])
                                                            ->required()
                                                            ->native(false)
                                                            ->placeholder('Select account type')
                                                            ->helperText('Choose deliverer category'),
                                                    ]),

                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 2])
                                                    ->columns(['md' => 2])
                                                    ->schema([
                                                        PhoneInput::make('phone_number'),
                                                        TextInput::make('email')
                                                            ->email()
                                                            ->placeholder('john@example.com')
                                                            ->prefixIcon('heroicon-o-envelope'),

                                                        TextInput::make('address')
                                                            ->columnSpan(['md' => 2])
                                                            ->placeholder('123 Main St, City, Country')
                                                            ->prefixIcon('heroicon-o-map-pin')
                                                            ->helperText('Full delivery address'),
                                                    ]),
                                            ]),
                                    ])
                                    ->helperText('Select existing delivery partner or create new')
                                    ->columnSpan(['md' => 2]),

                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->label('Customer')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->createOptionForm([
                                        Section::make('Customer Information')
                                            ->columns(['md' => 2, 'lg' => 3])
                                            ->schema([
                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 1])
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->placeholder('John Smith')
                                                            ->prefixIcon('heroicon-o-user')
                                                            ->columnSpanFull(),
                                                    ]),

                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 2])
                                                    ->columns(['md' => 2])
                                                    ->schema([
                                                        PhoneInput::make('phone'),

                                                        TextInput::make('email')
                                                            ->email()
                                                            ->placeholder('customer@example.com')
                                                            ->prefixIcon('heroicon-o-envelope'),

                                                        TextInput::make('address')
                                                            ->columnSpan(['md' => 2])
                                                            ->placeholder('123 Main Street, City, Country')
                                                            ->prefixIcon('heroicon-o-map-pin')
                                                            ->helperText('Full customer address'),
                                                    ]),
                                            ]),
                                    ])
                                    ->editOptionForm([
                                        Section::make('Customer Information')
                                            ->columns(['md' => 2, 'lg' => 3])
                                            ->schema([
                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 1])
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->prefixIcon('heroicon-o-user'),
                                                    ]),

                                                Grid::make()
                                                    ->columnSpan(['md' => 1, 'lg' => 2])
                                                    ->columns(['md' => 2])
                                                    ->schema([
                                                        TextInput::make('phone')
                                                            ->tel()
                                                            ->required()
                                                            ->prefixIcon('heroicon-o-phone'),

                                                        TextInput::make('email')
                                                            ->email()
                                                            ->required()
                                                            ->prefixIcon('heroicon-o-envelope'),

                                                        TextInput::make('address')
                                                            ->columnSpan(['md' => 2])
                                                            ->required()
                                                            ->prefixIcon('heroicon-o-map-pin'),
                                                    ]),
                                            ]),
                                    ])
                                    ->helperText('Select existing customer or create new')
                                    ->columnSpan(['md' => 2]),

                                TextInput::make('invoice_number')
                                    ->default(fn () => 'INV-' . str_pad((Sale::max('id') + 1), 4, '0', STR_PAD_LEFT))
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('INV-0001')
                                    ->helperText('Unique invoice identifier'),

                                DatePicker::make('sale_date')
                                    ->default(now())
                                    ->required()
                                    ->displayFormat('M d, Y'),

                                Select::make('payment_method')
                                    ->options(PaymentMethod::class)
                                    ->required()
                                    ->columnSpan(['md' => 2]),

                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                        'returned' => 'Returned',
                                        'refunded' => 'Refunded',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->columnSpan(['md' => 2]),

                                Select::make('delivery_status')
                                    ->label('Delivery Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'delivered' => 'Delivered',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->columnSpan(['md' => 2]),
                            ]),

                        Repeater::make('saleItems')
                            ->relationship('saleItems')
                            ->label('Sale Items')
                            ->columnSpan(['lg' => 3])
                            ->schema([
                                TextInput::make('barcode')
                                    ->label('Scan Barcode')
                                    ->placeholder('Scan or enter barcode')
                                    ->helperText('Scan product barcode to auto-select')
                                    ->maxLength(255)
                                    ->suffixAction(
                                        Action::make('scanBarcode')
                                            ->icon('heroicon-o-qr-code')
                                            ->action(function ($component, Set $set, Get $get) {
                                                $barcode = $component->getState();
                                                $product = Product::where('barcode', $barcode)
                                                    ->where('sellable', true)
                                                    ->first();

                                                if (!$product) {
                                                    Notification::make()
                                                        ->title('Product not found!')
                                                        ->body("No sellable product found with barcode: $barcode")
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                $set('product_id', $product->id);
                                                $set('unit_price', $product->price);
                                                $set('quantity', 1);

                                                // Clear barcode field
                                                $component->state(null);

                                                // Update item total and trigger global calculation
                                                self::updateSaleItemTotal($get, $set);
                                                self::runCalculations($get, $set, '../../');

                                                Notification::make()
                                                    ->title('Product added!')
                                                    ->body($product->name)
                                                    ->success()
                                                    ->send();
                                            })
                                    )
                                    ->columnSpan(2)
                                    ->live(),

                                Select::make('product_id')
                                    ->relationship(
                                        'product',
                                        'name',
                                        fn (Builder $query) => $query->where('sellable', true)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->allowHtml()
                                    ->getOptionLabelFromRecordUsing(function (Product $product) {
                                        return view('filament.forms.components.product-select-label', [
                                            'name' => $product->name,
                                            'image' => $product->attachments,
                                            'price' => $product->price,
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
                                            $set('unit_price', $product->price);
                                            $set('quantity', 1);
                                            $set('barcode', null);

                                            self::updateSaleItemTotal($get, $set);
                                            self::runCalculations($get, $set, '../../');

                                            Notification::make()
                                                ->title('Product Selected')
                                                ->body("Selected {$product->name}")
                                                ->success()
                                                ->send();
                                        }
                                    }),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(fn (Get $get) => Product::find($get('product_id'))?->quantity)
                                    ->columnSpan(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateSaleItemTotal($get, $set);
                                        self::runCalculations($get, $set, '../../');
                                    }),

                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->columnSpan(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateSaleItemTotal($get, $set);
                                        self::runCalculations($get, $set, '../../');
                                    }),

                                TextInput::make('total')
                                    ->prefix('$')
                                    ->readOnly()
                                    ->numeric()
                                    ->columnSpan(1)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                                    ->dehydrated(),
                            ])
                            ->columns(4)
                            ->itemLabel(fn (array $state): ?string =>
                                Product::find($state['product_id'])?->name ?? null)
                            ->addActionLabel('Add Item')
                            ->defaultItems(1)
                            ->collapsible()
                            ->cloneable()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::runCalculations($get, $set, '../../');
                            }),

                        // Financial summary section
                        Section::make('Financial Summary')
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                TextInput::make('subtotal')
                                    ->prefix('$')
                                    ->readOnly()
                                    ->numeric()
                                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                                    ->dehydrated(),

                                TextInput::make('delivery_fee')
                                    ->prefix('$')
                                    ->numeric()
                                    ->default($defaultDeliveryFee) // Set default from settings
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::runCalculations($get, $set)),

                                TextInput::make('discount')
                                    ->prefix('$')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::runCalculations($get, $set)),

                                TextInput::make('tax')
                                    ->prefix('$')
                                    ->numeric()
                                    ->default(function (Get $get) use ($initialDefaultTaxRate) { // <-- Corrected variable name here
                                        // Calculate initial tax based on subtotal and default tax rate
                                        $subtotal = collect($get('saleItems') ?? [])->sum(function ($item) {
                                            return ((float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0));
                                        });
                                        return round($subtotal * ($initialDefaultTaxRate / 100), 2); // <-- Corrected variable name here
                                    })
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::runCalculations($get, $set)),

                                TextInput::make('total_amount')
                                    ->prefix('$')
                                    ->readOnly()
                                    ->numeric()
                                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                                    ->dehydrated()
                                    ->suffixAction(
                                        Action::make('calculateTotals')
                                            ->icon('heroicon-o-calculator')
                                            ->color('primary')
                                            ->action(function (Get $get, Set $set) {
                                                if (!self::validateCalculationInputs($get)) {
                                                    return;
                                                }
                                                self::runCalculations($get, $set);
                                                Notification::make()
                                                    ->title('Totals Updated')
                                                    ->body('All calculations completed successfully')
                                                    ->success()
                                                    ->send();
                                            })
                                            ->tooltip('Recalculate All Totals')
                                    ),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('deliverer.name')
                    ->label(__('Deliverer'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('deliverer', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->icon('heroicon-o-truck')
                    ->default(__('N/A'))
                    ->url(fn ($record) => $record->deliverer_id ? DelivererResource::getUrl('edit', ['record' => $record->deliverer_id]) : null),
                
                TextColumn::make('product.subcategory.name')
                    ->label('Sub Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('product.brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('delivery_status')
                    ->label(__('Delivery Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof DeliveryStatus ? $state->getLabel() : ucfirst(str_replace('_', ' ', $state)))
                    ->colors([
                        'pending' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    ])
                    ->sortable()
                    ->icon('heroicon-o-cube-transparent')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('customer.name')
                    ->label(__('Customer Name'))
                    ->default(__('N/A'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('customer', fn(Builder $q) => $q->where('phone', 'like', "%{$search}%"));
                    })
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-circle'),
                    
                TextColumn::make('payment_method')
                    ->badge()
                    ->colors([
                        'cash' => 'success',
                        'card' => 'primary',
                        'transfer' => 'warning',
                    ])
                    ->icons([
                        'cash' => 'heroicon-o-banknotes',
                        'card' => 'heroicon-o-credit-card',
                        'transfer' => 'heroicon-o-arrow-up-right',
                    ])
                    ->sortable(),
                
                TextColumn::make('invoice_number')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-document-text'),

                TextColumn::make('sale_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable()
                    ->color('primary'),

                TextColumn::make('delivery_fee')
                    ->money('USD')
                    ->icon('heroicon-o-currency-dollar')
                    ->visibleFrom('lg'),

                TextColumn::make('discount')
                    ->money('USD')
                    ->color('danger')
                    ->icon('heroicon-o-arrow-down'),

                TextColumn::make('tax')
                    ->money('USD')
                    ->icon('heroicon-o-scale'),

                TextColumn::make('created_at')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('M d, Y h:i A'))
                    ->visibleFrom('md'),
            ])
            ->filters([
                // Filters can be added here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make("view_invoice")
                    ->label("View Invoice")
                    ->icon("heroicon-o-document")
                    ->url(fn ($record): mixed => Invoice::getUrl(['record' => $record->id]))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relations can be added here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'edit' => Pages\EditSales::route('/{record}/edit'),
            'invoice' => Invoice::route('/{record}/invoice'),
        ];
    }

    /**
     * Updates the total for a single sale item.
     */
    protected static function updateSaleItemTotal(Get $get, Set $set): void
    {
        $quantity = (float) $get('quantity');
        $unitPrice = (float) $get('unit_price');
        $total = round($quantity * $unitPrice, 2);
        $set('total', $total);
    }

    /**
     * Runs all grand total calculations for the sale.
     */
    protected static function runCalculations(Get $get, Set $set, string $parentPath = ''): void
    {
        $saleItems = $get($parentPath . 'saleItems');
        $subtotal = 0;
    
        if (is_array($saleItems)) {
            foreach ($saleItems as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $subtotal += $quantity * $unitPrice;
            }
        }
    
        $companyId = auth()->user()->current_company_id ?? config('app.company_id', 1);
        $defaultTaxRatePercentage = (float) Setting::where('company_id', $companyId)->where('key', 'tax_rate')->value('value') * 100;
    
        $deliveryFee = (float) ($get($parentPath . 'delivery_fee') ?? 0);
        $discount = (float) ($get($parentPath . 'discount') ?? 0);
    
        // Calculate tax based on the subtotal and the default tax rate.
        // This will *always* recalculate the tax if defaultTaxRatePercentage is set and subtotal > 0.
        // If you want to allow manual override, consider removing the defaultTaxRatePercentage usage here
        // and rely solely on the initial default value, or introduce a "manual_tax_override" flag.
        $calculatedTax = 0;
        if ($defaultTaxRatePercentage > 0 && $subtotal > 0) {
            $calculatedTax = round($subtotal * ($defaultTaxRatePercentage / 100), 2);
        }
        // Set the calculated tax value to the form field.
        $set($parentPath . 'tax', $calculatedTax);
    
        // Ensure the $tax variable used in total calculation is the newly calculated one
        $tax = $calculatedTax;
    
    
        // Calculate total
        $totalAmount = round($subtotal + $deliveryFee + $tax - $discount, 2);
    
        // Set parent fields
        $set($parentPath . 'subtotal', $subtotal);
        $set($parentPath . 'total_amount', $totalAmount);
    }

    /**
     * Validates inputs before running grand total calculations.
     */
    protected static function validateCalculationInputs(Get $get): bool
    {
        $saleItems = $get('saleItems');

        if (empty($saleItems)) {
            Notification::make()
                ->title('No Items')
                ->body('Please add at least one item to calculate totals.')
                ->warning()
                ->send();
            return false;
        }

        foreach ($saleItems as $index => $item) {
            $itemNumber = $index + 1;
            
            if (empty($item['product_id'])) {
                Notification::make()
                    ->title("Item #{$itemNumber}: Product Missing")
                    ->body('Please select a product for this item.')
                    ->warning()
                    ->send();
                return false;
            }
            
            if (!is_numeric($item['quantity']) || (float)$item['quantity'] <= 0) {
                Notification::make()
                    ->title("Item #{$itemNumber}: Invalid Quantity")
                    ->body('Quantity must be a positive number.')
                    ->warning()
                    ->send();
                return false;
            }
            
            if (!is_numeric($item['unit_price']) || (float)$item['unit_price'] < 0) {
                Notification::make()
                    ->title("Item #{$itemNumber}: Invalid Price")
                    ->body('Unit price must be a non-negative number.')
                    ->warning()
                    ->send();
                return false;
            }
        }

        // Validate financial fields
        $fields = ['delivery_fee', 'tax', 'discount'];
        foreach ($fields as $field) {
            if (!is_numeric($get($field)) || (float)$get($field) < 0) {
                Notification::make()
                    ->title("Invalid {$field}")
                    ->body(ucfirst($field) . ' must be a non-negative number.')
                    ->warning()
                    ->send();
                return false;
            }
        }

        return true;
    }
}