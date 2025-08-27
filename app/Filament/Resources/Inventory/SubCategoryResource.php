<?php

namespace App\Filament\Resources\Inventory;

use App\Filament\Resources\Inventory\SubCategoryResource\Pages;
use App\Filament\Resources\Inventory\SubCategoryResource\RelationManagers;
use App\Models\Inventory\Category;
use App\Models\Inventory\SubCategory;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class SubCategoryResource extends Resource
{
    protected static ?string $model = SubCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()                      
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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(
                        table: SubCategory::class,
                        column: 'name',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', auth()->user()->current_company_id);
                        }
                    )
                    ->maxLength(255),
            ]);
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Subcategory')
                    ->searchable()
                    ->sortable(),
    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
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
            'index' => Pages\ListSubCategories::route('/'),
            'create' => Pages\CreateSubCategory::route('/create'),
            'edit' => Pages\EditSubCategory::route('/{record}/edit'),
        ];
    }
}
