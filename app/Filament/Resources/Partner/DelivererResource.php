<?php

namespace App\Filament\Resources\Partner;

use App\Filament\Resources\Partner\DelivererResource\Pages;
use App\Filament\Resources\Delivery\DelivererResource\RelationManagers;
use App\Models\Partner\Deliverer;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class DelivererResource extends Resource
{
    protected static ?string $model = Deliverer::class;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
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

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->sortable(),

            TextColumn::make('type')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'personal' => 'success',
                    'institution' => 'warning',
                })
                ->sortable()
                ->icon(fn (string $state): string => match ($state) {
                    'personal' => 'heroicon-o-user',
                    'institution' => 'heroicon-o-building-office',
                }),

            TextColumn::make('phone_number')
                ->searchable()
                ->sortable()
                ->url(fn (Deliverer $record): string => "tel:{$record->phone}")
                ->icon('heroicon-o-phone')
                ->copyable(),

            TextColumn::make('email')
                ->searchable()
                ->sortable()
                ->url(fn (Deliverer $record): string => "mailto:{$record->email}")
                ->icon('heroicon-o-envelope')
                ->copyable()
                ->visibleFrom('md'),

            TextColumn::make('address')
                ->searchable()
                ->sortable()
                ->limit(30)
                ->icon('heroicon-o-map-pin')
                ->visibleFrom('lg'),
        ])
        ->filters([
            SelectFilter::make('type')
                ->options([
                    'personal' => 'Personal',
                    'institution' => 'Institution',
                ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\ViewAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
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
            'index' => Pages\ListDeliverers::route('/'),
            'create' => Pages\CreateDeliverer::route('/create'),
            'edit' => Pages\EditDeliverer::route('/{record}/edit'),
        ];
    }
}
