<?php

namespace App\Filament\Member\Resources\FavoriteResource\Pages;

use App\Filament\Member\Resources\FavoriteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFavorites extends ManageRecords
{
  protected static string $resource = FavoriteResource::class;

  protected function getHeaderActions(): array
  {
    return [
      // Actions\CreateAction::make(),
    ];
  }
}
