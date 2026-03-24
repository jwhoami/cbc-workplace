<?php

namespace App\Filament\Member\Resources\CandidateProfileResource\Pages;

use App\Filament\Member\Resources\CandidateProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditCandidateProfile extends EditRecord
{
  protected static string $resource = CandidateProfileResource::class;

  protected function getRedirectUrl(): string
  {
    return CandidateProfileResource::getUrl('edit', ['record' => $this->record]);
  }

  protected function getSavedNotificationTitle(): ?string
  {
    return __('models/candidate-profile.notifications.updated');
  }
}
