<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleResource\Pages;
use App\Filament\Admin\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoleResource extends Resource
{
  protected static ?string $model = Role::class;

  protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

  protected static bool $shouldRegisterNavigation = false;

  protected static ?string $modelLabel = 'Rol';

  protected static ?string $pluralModelLabel = 'Roles';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(50),
        Forms\Components\TextInput::make('title')
          ->maxLength(100),
        Forms\Components\Toggle::make('is_active')
          ->required(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('id')
          ->label(__('Id'))
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->label(__('Nombre'))
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('title')
          ->label(__('Título'))
          ->sortable()
          ->searchable(),
        Tables\Columns\IconColumn::make('is_active')
          ->label(__('Activo'))
          ->boolean()
          ->action(function (Role $record): void {
            abort_if(!auth()->user()->hasPermission('role.toggleflag-active'), 401);
            $record->is_active = !$record->is_active;
            $record->save();
          }),
        Tables\Columns\IconColumn::make('is_admin')
          ->label(__('Admin'))
          ->boolean()
          ->action(function (Role $record): void {
            abort_if(!auth()->user()->hasPermission($record, 'role.toggleflag-admin'), 401);
            $record->is_admin = !$record->is_admin;
            $record->save();
          }),
      ])
      ->filters([
        Tables\Filters\TernaryFilter::make('is_active')
          ->label(__('Activo')),
        Tables\Filters\TernaryFilter::make('is_admin')
          ->label(__('Admin')),
      ])
      ->actions([
        Tables\Actions\EditAction::make()
          ->label(false)
          ->toolTip(__('Editar')),
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make('configure')
            ->icon('heroicon-o-cog')
            ->label(__('Configurar'))
            ->url(fn (Role $record): string => RoleResource::getUrl('configure', ['role' => $record->id])),
          Tables\Actions\DeleteAction::make()
            ->label(__('Eliminar')),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ])
      ->emptyStateActions([
        Tables\Actions\CreateAction::make(),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageRoles::route('/'),
      'configure' => Pages\Configure::route('/{role}/configure'),
    ];
  }
}
