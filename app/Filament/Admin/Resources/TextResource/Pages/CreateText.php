<?php

namespace App\Filament\Admin\Resources\TextResource\Pages;

use App\Filament\Admin\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateText extends CreateRecord
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
    ];
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $data['code'] = Str::slug($data['title']);

    return $data;
  }
}
