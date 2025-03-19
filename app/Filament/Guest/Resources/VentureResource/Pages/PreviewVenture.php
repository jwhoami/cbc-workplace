<?php

namespace App\Filament\Guest\Resources\VentureResource\Pages;

use App\Filament\Guest\Resources\VentureResource;
use App\Helpers\Util;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class PreviewVenture extends ViewRecord
{
  protected static string $resource = VentureResource::class;

  public $returnPanel = "member";

  public function getTitle(): string | Htmlable
  {
    return ' ';
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('back')
        ->label(__('common.actions.back.label'))
        ->tooltip(__('common.actions.back.tooltip'))
        ->color('gray')
        ->action(function () {
          $url = str(VentureResource::getUrl('view', [$this->record]))->replace('ventures/', "{$this->returnPanel}/ventures/")->value();
          redirect($url);
        }),
    ];
  }

  public function mount(int | string $record): void
  {
    parent::mount($record);
    $this->returnPanel = Request::input('panel', 'member');
    if (! $this->record->preview_until) {
      Util::filamentNotification(__("Vista previa esta deshabilitada"), "warning");
      $this->redirect('/');
      return;
    }
    if (Carbon::now()->isAfter($this->record->preview_until)) {
      Util::filamentNotification(__("Vista previa esta deshabilitada"), "warning");
      $this->redirect('/');
      return;
    }
  }
}
