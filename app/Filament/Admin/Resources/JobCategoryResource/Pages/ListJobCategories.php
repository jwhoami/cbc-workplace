<?php

namespace App\Filament\Admin\Resources\JobCategoryResource\Pages;

use App\Filament\Admin\Resources\JobCategoryResource;
use App\Helpers\Util;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListJobCategories extends ListRecords
{
  protected static string $resource = JobCategoryResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalWidth('md')
        ->label(__('Crear'))
        ->mutateFormDataUsing(function (array $data): array {
          $data['scope'] = 'JobListing';
          if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
          }
          return $data;
        })
        ->after(function () {
          Util::filamentNotification(__('models/category.notifications.created'));
        }),
    ];
  }
}
