<?php

namespace App\Filament\Member\Resources;

use App\Filament\Member\Resources\VentureResource\Pages;
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
    return parent::getEloquentQuery()->where('member_id', auth()->user()->id);
  }
}
