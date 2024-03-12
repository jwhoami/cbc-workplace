<?php

namespace App\Filament\Shared\Resources\BaseVentureResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class BaseEditVenture extends EditRecord
{
  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list')
        ->label(__('common.actions.goto-list.label'))
        ->tooltip(__('common.actions.goto-list.tooltip'))
        ->color('gray')
        ->url(static::$resource::getUrl('index')),
      Actions\ViewAction::make()
        ->label(__('common.actions.view.label'))
        ->tooltip(__('common.actions.view.tooltip'))
    ];
  }
}
