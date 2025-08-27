<?php

namespace App\Filament\Resources\Inventory;

use App\Filament\Resources\Inventory\BatchesResource\Pages;
use App\Models\Inventory\Batch;
use App\Models\Inventory\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BatchesResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function generateBatchNumber(): string
    {
        $prefix = 'BAT';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$timestamp}-{$random}";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Batch Information')
                    ->columns(['md' => 2])
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->allowHtml()
                            ->getOptionLabelFromRecordUsing(function (Product $product) {
                                return view('filament.forms.components.product-select-label', [
                                    'name' => $product->name,
                                    'image' => $product->attachments,
                                    'price' => $product->price,
                                    'stock' => $product->stock,
                                    'subcategory_name' => $product->subcategory->name ?? 'N/A',
                                    'unit_type' => $product->unit_type
                                ])->render();
                            })
                            ->preload(),

                        TextInput::make('batch_number')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter batch number')
                            ->default(fn () => self::generateBatchNumber())
                            ->helperText('Auto-generated but can be edited')
                            ->unique(Batch::class, 'batch_number', ignoreRecord: true),

                        TextInput::make('cost_price')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->step(0.01),

                        TextInput::make('quantity')
                            ->numeric()
                            ->required(),
                        
                        DatePicker::make('expiry_date')
                            ->native(false)
                            ->helperText('Can leave blank if product has no expiry date')
                            ->minDate(now()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable()
                    ->label('Product'),

                TextColumn::make('batch_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy'),

                TextColumn::make('cost_price')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('remaining_quantity')
                    ->label('Remaining Stock')
                    ->sortable()
                    ->numeric(),


                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->color(fn (Batch $record) => 
                        $record->expiry_date->isPast() ? 'danger' :
                        ($record->expiry_date->diffInDays(now()) < 30 ? 'warning' : 'success')
                    ),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatches::route('/create'),
            'edit' => Pages\EditBatches::route('/{record}/edit'),
        ];
    }
}
