<?php

namespace App\Filament\Admin\Resources\TextResource\Pages;

use App\Filament\Admin\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditText extends EditRecord
{
  protected static string $resource = TextResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list')
        ->label(__('common.actions.goto-list.label'))
        ->tooltip(__('common.actions.goto-list.tooltip'))
        ->color('gray')
        ->url(TextResource::getUrl('index')),
      Actions\ViewAction::make()
        ->label(__('common.actions.view.label'))
        ->tooltip(__('common.actions.view.tooltip'))
    ];
  }
}
