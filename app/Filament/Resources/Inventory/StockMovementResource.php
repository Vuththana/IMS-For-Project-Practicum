<?php

namespace App\Filament\Resources\Inventory;

use App\Filament\Resources\Inventory\StockMovementResource\Pages;
use App\Models\Inventory\StockMovement;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ProductResource::getUrl('edit', [$record->product_id]))
                    ->icon('heroicon-o-cube'),
    
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'in' => 'success',
                        'out' => 'danger',
                    ])
                    ->icons([
                        'in' => 'heroicon-o-arrow-down-tray',
                        'out' => 'heroicon-o-arrow-up-tray',
                    ])
                    ->sortable(),
    
                TextColumn::make('quantity')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->direction === 'in' ? "+$state" : "-$state"
                    )
                    ->color(fn ($record) => 
                        $record->direction === 'in' ? 'success' : 'danger'
                    ),
    
                TextColumn::make('note')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->note)
                    ->icon('heroicon-o-document-text'),
    
                TextColumn::make('moved_at')
                    ->date('M d, Y')
                    ->sortable()
                    ->tooltip(fn ($record) => $record->moved_at->format('M d, Y h:i A'))
                    ->icon('heroicon-o-calendar'),
    
                TextColumn::make('created_at')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('M d, Y h:i A'))
                    ->visibleFrom('lg'),
            ])
            ->filters([
                // You could add filters for movement type or date range here
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
            ]);
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
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
        ];
    }
}
