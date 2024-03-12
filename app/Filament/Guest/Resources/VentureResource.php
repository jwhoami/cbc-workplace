<?php

namespace App\Filament\Guest\Resources;

use App\Enums\ApprovalState;
use App\Filament\Guest\Resources\VentureResource\Pages;
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
      'create' => Pages\CreateVenture::route('/create'),
      'view' => Pages\ViewVenture::route('/{record}'),
      'edit' => Pages\EditVenture::route('/{record}/edit'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()->where('approval_state', ApprovalState::APPROVED);
  }
}
