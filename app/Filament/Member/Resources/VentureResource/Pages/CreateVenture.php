<?php

namespace App\Filament\Member\Resources\VentureResource\Pages;

use App\Enums\MembershipState;
use App\Filament\Member\Resources\VentureResource;
use App\Filament\Shared\Resources\BaseVentureResource\Pages\BaseCreateVenture;
use App\Helpers\Util;
use Filament\Facades\Filament;

class CreateVenture extends BaseCreateVenture
{
  protected static string $resource = VentureResource::class;

  public function mount(): void
  {
    parent::mount();
    if (Filament::auth()->user()->membership_state !== MembershipState::APPROVED) {
      Util::filamentNotification(__('Usted debe afiliarse para poder publicar su emprendimientos'), 'warning');
      $this->redirect('/member/profile');
    }
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $data['member_id'] = auth()->id();
    $data['is_active'] = false;
    $data['is_expired'] = false;
    if (! ($data['expires_at'] ?? null)) {
      $data['expires_at'] = now()->addDays(90);
      $data['is_extendable'] = true;
    }

    return $data;
  }
}
