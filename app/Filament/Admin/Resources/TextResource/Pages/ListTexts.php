<?php

namespace App\Filament\Admin\Resources\TextResource\Pages;

use App\Filament\Admin\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTexts extends ListRecords
{
  protected static string $resource = TextResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label(__('common.actions.create.label'))
        ->tooltip(__('common.actions.create.tooltip'))
    ];
  }
}
