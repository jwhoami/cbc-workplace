<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VentureResource\Pages;
use App\Filament\Admin\Resources\VentureResource\RelationManagers\CommentsRelationManager;
use App\Filament\Admin\Resources\VentureResource\RelationManagers\MediaRelationManager;
use App\Filament\Shared\Resources\BaseVentureResource;
use Illuminate\Database\Eloquent\Builder;

class VentureResource extends BaseVentureResource
{

  protected static ?string $navigationIcon = 'heroicon-o-chevron-right';

  protected static ?string $navigationGroup = "Emprendimientos";


  public static function getRelations(): array
  {
    return [
      MediaRelationManager::class,
      CommentsRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListVentures::route('/'),
      'view' => Pages\ViewVenture::route('/{record}'),
      // 'create' => Pages\CreateVenture::route('/create'),
      'edit' => Pages\EditVenture::route('/{record}/edit'),
      'edit-categories' => Pages\EditCategories::route('/{record}/edit-categories'),
    ];
  }

  // public static function getEloquentQuery(): Builder
  // {
  //   return parent::getEloquentQuery()->whereNot('approval_state', VentureApprovalState::NEW);
  // }
}
