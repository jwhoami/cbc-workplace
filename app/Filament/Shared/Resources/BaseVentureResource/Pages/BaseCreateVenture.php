<?php

namespace App\Filament\Shared\Resources\BaseVentureResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class BaseCreateVenture extends CreateRecord
{
  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list')
        ->label(__('common.actions.goto-list.label'))
        ->tooltip(__('common.actions.goto-list.tooltip'))
        ->color('gray')
        ->url(static::$resource::getUrl('index')),
    ];
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $data['member_id'] = auth()->user()->id;

    return $data;
  }
}
