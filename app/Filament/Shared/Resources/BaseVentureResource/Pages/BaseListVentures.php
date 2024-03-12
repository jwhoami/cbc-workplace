<?php

namespace App\Filament\Shared\Resources\BaseVentureResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class BaseListVentures extends ListRecords
{
  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label(__('common.actions.create.label'))
        ->tooltip(__('common.actions.create.tooltip'))
    ];
  }
}
