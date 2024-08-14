<?php

namespace App\Filament\Shared\Resources\BaseVentureResource\Pages;

use App\Helpers\Util;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

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

  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    $record->categories
      ->each(function (Category $category) use ($record) {
        $record->categories()->detach($category);
      });
    $categories = $data['category'] ?? [];
    unset($data['category']);
    $record->update($data);
    $record->save();
    foreach ($categories as $id) {
      $category = Category::find($id);
      $record->categories()->attach($category);
    }
    return $record;
  }

}
