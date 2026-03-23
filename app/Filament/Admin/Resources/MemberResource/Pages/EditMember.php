<?php

namespace App\Filament\Admin\Resources\MemberResource\Pages;

use App\Filament\Admin\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
  protected static string $resource = MemberResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list.label')
        ->label(__('common.actions.goto-list.label'))
        ->tooltip(__('common.actions.goto-list.tooltip'))
        ->color('gray')
        ->url(MemberResource::getUrl('index')),
      Actions\ViewAction::make()
        ->label(__('common.actions.view.label'))
        ->tooltip(__('common.actions.view.tooltip'))
    ];
  }
}
