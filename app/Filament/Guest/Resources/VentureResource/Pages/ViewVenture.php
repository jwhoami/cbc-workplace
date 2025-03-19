<?php

namespace App\Filament\Guest\Resources\VentureResource\Pages;

use App\Filament\Guest\Resources\VentureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewVenture extends ViewRecord
{
  protected static string $resource = VentureResource::class;

  public function getTitle(): string | Htmlable
  {
    return ' ';
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('goto-list')
        ->label(__('common.actions.back.label'))
        ->tooltip(__('common.actions.back.tooltip'))
        ->color('gray')
        ->url(static::$resource::getUrl('index')),
    ];
  }
}
