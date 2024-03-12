<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ApprovalState;
use App\Filament\Admin\Resources\VentureResource\Pages;
use App\Filament\Shared\Resources\BaseVentureResource;
use Illuminate\Database\Eloquent\Builder;

class VentureResource extends BaseVentureResource
{
  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListVentures::route('/'),
      'view' => Pages\ViewVenture::route('/{record}'),
      // 'create' => Pages\CreateVenture::route('/create'),
      // 'edit' => Pages\EditVenture::route('/{record}/edit'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()->whereNot('approval_state', ApprovalState::UNDEFINED);
  }
}
