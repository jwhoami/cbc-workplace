<?php

namespace App\Filament\Member\Resources\OrganizationResource\Pages;

use App\Filament\Member\Resources\OrganizationResource;
use App\Models\Organization;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrganization extends CreateRecord
{
    protected static string $resource = OrganizationResource::class;

    public function mount(): void
    {
        $existing = Organization::where('member_id', auth('member')->id())->first();

        if ($existing) {
            $this->redirect(OrganizationResource::getUrl('edit', ['record' => $existing]));

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
        return OrganizationResource::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('models/organization.notifications.created');
    }
}
