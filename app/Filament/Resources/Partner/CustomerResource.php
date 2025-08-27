<?php

namespace App\Filament\Resources\Partner;

use App\Filament\Resources\Partner\CustomerResource\Pages;
use App\Filament\Resources\Partner\CustomerResource\RelationManagers;
use App\Models\Partner\Customer;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('name')->searchable()->sortable(),
               TextColumn::make('phone')->searchable(),
               TextColumn::make('email')->searchable(),
               TextColumn::make('created_at')->dateTime('M d, Y')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
