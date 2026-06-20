<?php

namespace App\Filament\Member\Resources\OrganizationResource\Pages;

use App\Filament\Member\Resources\OrganizationResource;
use App\Models\Organization;
use Filament\Resources\Pages\ListRecords;

class ListOrganizations extends ListRecords
{
    protected static string $resource = OrganizationResource::class;

    public function mount(): void
    {
        parent::mount();

        $organization = Organization::where('member_id', auth('member')->id())->first();

        if ($organization) {
            $this->redirect(OrganizationResource::getUrl('edit', ['record' => $organization]));
        } else {
            $this->redirect(OrganizationResource::getUrl('create'));
        }
    }
}
