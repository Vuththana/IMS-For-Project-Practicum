<?php

namespace App\Filament\Resources\Inventory;

use App\Filament\Resources\Inventory\BrandResource\Pages;
use App\Models\Inventory\Brand;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Brand Details')
                    ->columns(3)
                    ->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Brand Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(
                                        table: Brand::class,
                                        column: 'name',
                                        ignoreRecord: true,
                                        modifyRuleUsing: function (Unique $rule) {
                                            return $rule->where('company_id', auth()->user()->company_id);
                                        }
                                    ),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(4),
                            ]),

                        // Column 3 for the logo
                        Section::make()
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('logo_path')
                                    ->label('Brand Logo')
                                    ->image() // Specifies that we are uploading an image
                                    ->imageEditor() // Adds a nice image editor
                                    ->directory('brands') // Directory in your default filesystem disk (e.g., public/storage/brands)
                                    ->avatar() // Displays the upload in a circular preview
                                    ->placeholder('Upload brand logo'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Displays the logo image directly in the table
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular(), // Renders the image in a circle

                TextColumn::make('name')
                    ->label('Brand Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(40)
                    ->tooltip(fn (Brand $record): string => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /*
     * As with CategoryResource, the getEloquentQuery() method is not needed
     * because your 'CompanyScope' global scope is handling multi-tenancy.
     */

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}