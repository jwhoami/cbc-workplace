<?php

namespace App\Filament\Member\Resources\VentureResource\Pages;

use App\Enums\MembershipState;
use App\Filament\Member\Resources\VentureResource;
use App\Filament\Shared\Resources\BaseVentureResource\Pages\BaseViewVenture;
use App\Helpers\Util;
use Filament\Facades\Filament;
use Filament\Actions;

class ViewVenture extends BaseViewVenture
{
  protected static string $resource = VentureResource::class;

  public function mount(int|string $record): void
  {
    parent::mount($record);
    if (filament()->auth()->user()->membership_state !== MembershipState::APPROVED) {
      Util::filamentNotification(__('Usted debe afiliarse para poder publicar su emprendimientos'), 'warning');
      $this->redirect('/member/profile');
    }
  }

  public function preview(): string
  {
    $this->record->preview_until = now()->addSeconds(300);
    $this->record->save();
    $url = route('venture-home') . "/ventures/{$this->record->id}/preview";
    return $url;
  }
}
