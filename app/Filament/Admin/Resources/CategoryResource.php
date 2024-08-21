<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers;
use App\Helpers\Util;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
  protected static ?string $model = Category::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function getModelLabel(): string
  {
    return __('Categoría');
  }

  public static function getPluralModelLabel(): string
  {
    return __('Categorías');
  }

  public static function form(Form $form): Form
  {
    return $form
      ->columns(1)
      ->schema([
        Forms\Components\Select::make('scope')
          ->required()
          ->label(__('Para'))
          ->options([
            'Venture' => 'Emprendimiento',
          ]),
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(50)
          ->label(__('Nombre')),
        Forms\Components\TextInput::make('order')
          ->required()
          ->numeric()
          ->default(0)
          ->label(__('Orden')),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('id')
          ->searchable()
          ->label(__('Id')),
        Tables\Columns\TextColumn::make('name')
          ->searchable()
          ->label(__('Nombre')),
        Tables\Columns\TextColumn::make('scope')
          ->searchable()
          ->label(__('Para')),
        Tables\Columns\TextColumn::make('parent_id')
          ->searchable()
          ->label(__('Padre')),
        Tables\Columns\TextColumn::make('order')
          ->searchable()
          ->label(__('Orden')),
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\Action::make('add')
          ->label(false)
          ->modalWidth('md')
          ->form([
            Forms\Components\TextInput::make('name')
              ->required()
              ->maxLength(50)
              ->label(__('Nombre')),
            Forms\Components\TextInput::make('order')
              ->required()
              ->numeric()
              ->label(__('Orden')),
          ])
          ->icon('heroicon-o-plus')
          ->action(function(Category $record, array $data) {
            $data['scope'] = $record->scope;
            $data['parent_id'] = $record->id;
            Category::create($data);
            Util::filamentNotification("!OPERATION-SUCCESS");
          }),
        Tables\Actions\EditAction::make()
          ->label(false),
        Tables\Actions\DeleteAction::make()
          ->label(false),
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
      'index' => Pages\ListCategories::route('/'),
//      'create' => Pages\CreateCategory::route('/create'),
//      'edit' => Pages\EditCategory::route('/{record}/edit'),
    ];
  }
}
