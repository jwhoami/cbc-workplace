<?php

namespace App\Filament\Member\Resources\JobListingResource\Pages;

use App\Enums\OrganizationVerificationState;
use App\Filament\Member\Resources\JobListingResource;
use App\Helpers\Util;
use App\Models\Organization;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateJobListing extends CreateRecord
{
    protected static string $resource = JobListingResource::class;

    public function mount(): void
    {
        $organization = Organization::where('member_id', auth('member')->id())->first();

        if (! $organization || $organization->verification_state !== OrganizationVerificationState::VERIFIED) {
            Util::filamentNotification(__('models/job-listing.notifications.org_not_verified'), 'danger');
            $this->redirect(JobListingResource::getUrl('index'));

            return;
        }

        parent::mount();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $organization = Organization::where('member_id', auth('member')->id())->first();
        $data['organization_id'] = $organization->id;
        $data['member_id'] = auth('member')->id();

        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return JobListingResource::getUrl('edit', ['record' => $this->record]);
    }
}
