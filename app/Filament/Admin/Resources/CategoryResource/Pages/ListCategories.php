<?php

namespace App\Filament\Admin\Resources\CategoryResource\Pages;

use App\Filament\Admin\Resources\CategoryResource;
use App\Helpers\Util;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
  protected static string $resource = CategoryResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalWidth('md')
        ->label(__('Crear')),
      Actions\ActionGroup::make([
        Actions\Action::make('update-child-count')
          ->label(__('Actualizar Hijos'))
          ->icon('heroicon-o-chevron-right')
          ->action(function () {
            Category::updateChildCount();
            Util::filamentNotification("!OPERATION-SUCCESS");
          })
      ]),
    ];
  }
}
