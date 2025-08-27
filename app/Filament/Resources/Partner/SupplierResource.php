<?php

namespace App\Filament\Resources\Partner;

use App\Filament\Resources\Partner\SupplierResource\Pages;
use App\Filament\Resources\Partner\SupplierResource\RelationManagers;
use App\Models\Partner\Supplier;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Supplier Information')
                    ->columns(['md' => 2, 'lg' => 3])
                    ->schema([
                        Grid::make()
                            ->columnSpan(['md' => 1, 'lg' => 1])
                            ->schema([
                                    
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Supplier Corp.')
                                    ->helperText('Official business name')
                                    ->columnSpanFull(),
                            ]),
                        
                        Grid::make()
                            ->columnSpan(['md' => 1, 'lg' => 2])
                            ->columns(['md' => 2])
                            ->schema([
                                TextInput::make('contact_person')
                                    ->placeholder('John Doe')
                                    ->prefixIcon('heroicon-o-user')
                                    ->helperText('Primary contact person'),
                                
                                TextInput::make('email')
                                    ->email()
                                    ->placeholder('supplier@example.com')
                                    ->prefixIcon('heroicon-o-at-symbol'),
                                
                                TextInput::make('phone')
                                    ->tel()
                                    ->placeholder('+1 (555) 000-0000')
                                    ->prefixIcon('heroicon-o-phone'),
                                
                                Textarea::make('address')
                                    ->placeholder("123 Business St.\nCity, State\nCountry")
                                    ->rows(3)
                                    ->columnSpan(['md' => 2]),
                                
                                TextInput::make('tax_number')
                                    ->label('Tax ID')
                                    ->placeholder('XX-XXXXXXX')
                                    ->prefixIcon('heroicon-o-document-text')
                                    ->helperText('VAT or GST number')
                                    ->columnSpan(['md' => 2]),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                    
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->visibleFrom('md')
                    ->default('-'),
    
                TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->visibleFrom('sm'),
    
                TextColumn::make('address')
                    ->limit(30)
                    ->icon('heroicon-o-map-pin')
                    ->toggleable()
                    ->extraAttributes(['class' => 'text-gray-600'])
                    ->visibleFrom('lg'),
    
                TextColumn::make('tax_number')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'success' : 'gray')
                    ->icon(fn (?string $state): string => $state ? 'heroicon-o-document-check' : 'heroicon-o-document')
                    ->formatStateUsing(fn (?string $state) => $state ?: 'N/A')
                    ->copyable()
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->filters([
                TernaryFilter::make('has_tax_number')
                    ->label('Tax Number Registered')
                    ->placeholder('All')
                    ->trueLabel('With Tax Number')
                    ->falseLabel('Without Tax Number'),
                    
                TernaryFilter::make('has_contact_person')
                    ->label('Contact Person')
                    ->placeholder('All')
                    ->trueLabel('Has Contact Person')
                    ->falseLabel('No Contact Person')
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                // Tables\Actions\DeleteAction::make() ---> Comment for better query
                //     ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-o-trash'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->icon('heroicon-o-arrow-uturn-left'),
                ]),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
