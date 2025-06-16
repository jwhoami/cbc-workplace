<?php

namespace App\Filament\Venture\Resources\VentureResource\Pages;

use App\Filament\Venture\Resources\VentureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewVenture extends ViewRecord
{
  protected static string $resource = VentureResource::class;

  public function mount(int | string $record): void
  {
    parent::mount($record);
    $isMobile = app()->make("BrowserAgent")->isMobile();

    $this->record->load([
      'media' => function ($query) use ($isMobile) {
        $query->isActive()->where('is_mobile', $isMobile);
      }
    ]);

    $this->record->updateViewCount();
  }

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
      // Actions\Action::make('favorite')
      //   ->label(__('Favorito'))
      //   ->tooltip(__('Agregar a mis favoritos'))
      //   ->visible(function () {
      //     $user = Filament::getPanel('member')->auth()->user();
      //     return (bool) $user?->id;
      //   })
      //   ->action(function (Venture $record) {
      //     $user = Filament::getPanel('member')->auth()->user();
      //     if (! $user) {
      //       return;
      //     }
      //     try {
      //       $user->favorites()->create([
      //         'venture_id' => $record->id,
      //       ]);
      //       $record->updateFavoriteCount();
      //     } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
      //       Util::filamentNotification(__("Este emprendimiento ya esta en su favoritos"), "warning");
      //       return;
      //     } catch (\Exception $e) {
      //       Util::filamentNotification($e->getMessage(), "warning");
      //       return;
      //     }
      //     Util::filamentNotification("!OPERATION-SUCCESS");
      //   }),
    ];
  }
}
