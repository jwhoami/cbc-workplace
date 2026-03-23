<?php

namespace App\Filament\Shared\Resources\BaseVentureResource\Pages;

use App\Models\Venture;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class BaseListVentures extends ListRecords
{
  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label(__('common.actions.create.label'))
        ->tooltip(__('common.actions.create.tooltip'))
        ->visible(fn() => Filament::getCurrentPanel()->getId() === 'member'),
      //        ->requiresAuthorization('create', Venture::class)
      //        ->hasAuthorization('Member.createVenture')
      //        ->requiresAuthorization('Member.createVenture'),
    ];
  }
}
