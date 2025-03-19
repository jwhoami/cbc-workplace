<?php

namespace App\Filament\Admin\Resources\VentureResource\Pages;

use App\Filament\Admin\Resources\VentureResource;
use App\Filament\Shared\Resources\BaseVentureResource\Pages\BaseViewVenture;

class ViewVenture extends BaseViewVenture
{
  protected static string $resource = VentureResource::class;

  public function preview(): string
  {
    $this->record->preview_until = now()->addSeconds(60);
    $this->record->save();
    $url = "/ventures/{$this->record->id}/preview?panel=admin";
    return $url;
  }
}
