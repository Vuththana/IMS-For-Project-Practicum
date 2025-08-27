<?php

namespace App\Filament\Resources\Inventory;

use App\Enums\Category\UnitType;
use App\Filament\Resources\Inventory\ProductResource\Pages;
use App\Models\Inventory\Brand;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\SubCategory;
use DesignTheBox\BarcodeField\Forms\Components\BarcodeInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static float $usdToKhrRate = 4090;
    

    public static function generateSKUNumber(): string
    {
        $prefix = 'SKU';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(string: Str::random(4));
        return "{$prefix}-{$timestamp}-{$random}";
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Information')
                    ->columns(['md' => 2, 'lg' => 3])
                    ->schema([
                        FileUpload::make('attachments')
                            ->label('Product Image')
                            ->columnSpan(['md' => 1, 'lg' => 1])
                            ->directory('product-images')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5000)
                            ->imagePreviewHeight('250')
                            ->helperText('Max size: 5MB, Recommended ratio: 1:1'),
                            
                        Grid::make()
                            ->columnSpan(['md' => 1, 'lg' => 2])
                            ->columns(['md' => 2])
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpan(['md' => 2])
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter product name'),
                                
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->unique()
                                    ->maxLength(50)
                                    ->default(fn () => self::generateSKUNumber())
                                    ->placeholder('SKU-0001')
                                    ->required(),
                                
                                Textarea::make('description')
                                    ->columnSpan(['md' => 2])
                                    ->rows(3)
                                    ->placeholder('Product description...'),
                                
                                TextInput::make('price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->step(0.01),
                                    Grid::make()
                                    ->columnSpan(['md' => 2, 'lg' => 2])
                                    ->columns(2)
                                    ->schema([
                                        Checkbox::make('sellable')
                                            ->label('Sellable')
                                            ->helperText('Indicates that this product can be sold to customers.')
                                            ->required(fn (callable $get) => !$get('purchasable'))
                                            ->reactive()
                                            ->columnSpan(1),
                                        
                                        Checkbox::make('purchasable')
                                            ->label('Purchasable')
                                            ->required(fn (callable $get) => !$get('sellable'))
                                            ->helperText('Indicates that this product can be purchased from suppliers.')
                                            ->reactive()
                                            ->columnSpan(1),
                                    ]),
                                TextInput::make('barcode')
                                    ->helperText('Focus than scan your barcode'),
                                    
                            ]), 
                    ]),

                    Section::make('Category Settings')
                    ->columns(['md' => 2])
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->live() 
                            ->afterStateUpdated(function (Set $set) {
                                $set('subcategory_id', null);
                            })
                            ->createOptionForm([
                                Section::make('Category Details')
                                    ->columns(1) 
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Category Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Enter category name')
                                                    ->unique(
                                                        table: Category::class,
                                                        column: 'name',
                                                        ignoreRecord: true,
                                                        modifyRuleUsing: function (Unique $rule) {
                                                            return $rule->where('company_id', auth()->user()->current_company_id);
                                                        }
                                                    ),
                                                Textarea::make('description')
                                                    ->label('Description')
                                                    ->maxLength(500)
                                                    ->rows(3)
                                                    ->placeholder('Short category description...'),
                                            ]),
                                    ]),
                            ]),
                            
                            Select::make('subcategory_id')
                            ->label('Subcategory')
                            ->relationship(
                                'subcategory',
                                'name',
                                fn (Builder $query, Get $get) => $query->where('category_id', $get('category_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->nullable()
                            ->visible(fn (Get $get): bool => (bool) $get('category_id'))
                            ->createOptionForm([
                                Section::make('Sub Category Detail')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Subcategory Name')
                                            ->unique(
                                                table: Subcategory::class,
                                                column: 'name',
                                                modifyRuleUsing: function (Unique $rule, Get $get, \Livewire\Component $livewire) {
                                                    $categoryId = $livewire->data['category_id'];
                                                    return $rule->where('category_id', $categoryId);
                                                }
                                            ),
                                        // You can add other fields for the subcategory here
                                    ]),
                            ])
                            ->createOptionUsing(function (array $data, Get $get): int {
                                $categoryId = $get('category_id');
                        
                                $subcategory = Subcategory::create([
                                    'name' => $data['name'],
                                    'category_id' => $categoryId,
                                ]);
                        
                                // Return the ID of the newly created subcategory
                                return $subcategory->id;
                            }),
                        
                        Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name') // Assuming product belongsTo Brand
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->createOptionForm([
                                Section::make('Brand Detail')
                                    ->columns(1)
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255)         
                                                    ->unique(
                                                        table: Brand::class,
                                                        column: 'name',
                                                        ignoreRecord: true,
                                                        modifyRuleUsing: function (Unique $rule) {
                                                            return $rule->where('company_id', auth()->user()->current_company_id);
                                                        }
                                                    ),
                                            ]),
                                    ]),
                            ]),
                        
                        Select::make('unit_type')
                            ->options(UnitType::class)
                            ->native(false)
                            ->default(UnitType::PIECE)
                            ->searchable()
                            ->helperText('Select measurement unit'),
                        ]),
                ]);
    }

    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                ImageColumn::make('attachments')
                    ->label('Image')
                    ->toggleable()
                    ->size(40)
                    ->defaultImageUrl(url('https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg')),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record) => str($record->description)->limit(30))
                    ->wrap(),
                
                TextColumn::make('price')
                    ->money('USD')
                    ->description(fn (Product $record) => '៛'.number_format($record->price * self::$usdToKhrRate))
                    ->sortable(),

            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            // 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}