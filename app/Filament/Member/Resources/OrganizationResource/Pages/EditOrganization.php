<?php

namespace App\Filament\Member\Resources\OrganizationResource\Pages;

use App\Actions\Member\RequestOrganizationVerification;
use App\Enums\OrganizationVerificationState;
use App\Filament\Member\Resources\OrganizationResource;
use App\Helpers\Util;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganization extends EditRecord
{
  protected static string $resource = OrganizationResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('request-verification')
        ->label(__('actions/member.request-organization-verification.label'))
        ->icon('heroicon-o-shield-check')
        ->color('warning')
        ->visible(fn () => in_array($this->record->verification_state, [
          OrganizationVerificationState::PENDING,
          OrganizationVerificationState::SUSPENDED,
        ]))
        ->requiresConfirmation()
        ->action(function () {
          Util::run(function () {
            RequestOrganizationVerification::run($this->record);
            Util::filamentNotification(__('actions/member.request-organization-verification.success'));
          });
        }),
    ];
  }

  protected function getRedirectUrl(): string
  {
    return OrganizationResource::getUrl('edit', ['record' => $this->record]);
  }

  protected function getSavedNotificationTitle(): ?string
  {
    return __('models/organization.notifications.updated');
  }
}
