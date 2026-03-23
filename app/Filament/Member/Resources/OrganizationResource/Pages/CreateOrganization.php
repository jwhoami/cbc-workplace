<?php

namespace App\Filament\Member\Resources\OrganizationResource\Pages;

use App\Filament\Member\Resources\OrganizationResource;
use App\Helpers\Util;
use App\Models\Organization;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrganization extends CreateRecord
{
  protected static string $resource = OrganizationResource::class;

  public function mount(): void
  {
    $existing = Organization::where('member_id', auth()->id())->first();

    if ($existing) {
      $this->redirect(OrganizationResource::getUrl('edit', ['record' => $existing]));
      return;
    }

    parent::mount();
  }

  protected function handleRecordCreation(array $data): Model
  {
    $data['member_id'] = auth()->id();

    return static::getModel()::create($data);
  }

  protected function getRedirectUrl(): string
  {
    return OrganizationResource::getUrl('edit', ['record' => $this->record]);
  }

  protected function getCreatedNotificationTitle(): ?string
  {
    return __('models/organization.notifications.created');
  }
}
