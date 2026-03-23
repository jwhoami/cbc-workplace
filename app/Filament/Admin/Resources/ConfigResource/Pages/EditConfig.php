<?php

namespace App\Filament\Admin\Resources\ConfigResource\Pages;

use App\Filament\Admin\Resources\ConfigResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConfig extends EditRecord
{
  protected static string $resource = ConfigResource::class;

  protected function getActions(): array
  {
    return [
      Actions\DeleteAction::make(),
    ];
  }
}
