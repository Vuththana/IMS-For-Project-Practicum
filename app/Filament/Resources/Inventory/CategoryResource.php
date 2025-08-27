<?php

namespace App\Filament\Resources\Inventory;

use App\Filament\Resources\Inventory\CategoryResource\Pages;
use App\Models\Inventory\Category;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea; // CHANGED: Using Textarea instead of TextInput
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique; // ADDED: For unique validation rule

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Category Details')
                    ->columns(1) 
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),


                TextColumn::make('description')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (Category $record): string => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // ADDED: View action is good practice
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // ADDED: Row-level delete action
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}