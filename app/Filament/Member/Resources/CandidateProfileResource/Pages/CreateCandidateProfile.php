<?php

namespace App\Filament\Member\Resources\CandidateProfileResource\Pages;

use App\Filament\Member\Resources\CandidateProfileResource;
use App\Models\CandidateProfile;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCandidateProfile extends CreateRecord
{
    protected static string $resource = CandidateProfileResource::class;

    public function mount(): void
    {
        $existing = CandidateProfile::where('member_id', auth('member')->id())->first();

        if ($existing) {
            $this->redirect(CandidateProfileResource::getUrl('edit', ['record' => $existing]));

            return;
        }

        parent::mount();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $model = static::getModel();
        $instance = new $model;
        $instance->fill($data);
        $instance->member_id = auth('member')->id();
        $instance->save();

        return $instance;
    }

    protected function getRedirectUrl(): string
    {
        return CandidateProfileResource::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('models/candidate-profile.notifications.created');
    }
}
