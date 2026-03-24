<?php

namespace App\Filament\Member\Resources\CandidateProfileResource\Pages;

use App\Filament\Member\Resources\CandidateProfileResource;
use App\Models\CandidateProfile;
use Filament\Resources\Pages\ListRecords;

class ListCandidateProfiles extends ListRecords
{
  protected static string $resource = CandidateProfileResource::class;

  public function mount(): void
  {
    parent::mount();

    $profile = CandidateProfile::where('member_id', auth()->id())->first();

    if ($profile) {
      $this->redirect(CandidateProfileResource::getUrl('edit', ['record' => $profile]));
    } else {
      $this->redirect(CandidateProfileResource::getUrl('create'));
    }
  }
}
