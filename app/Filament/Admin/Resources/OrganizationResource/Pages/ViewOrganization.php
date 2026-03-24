<?php

namespace App\Filament\Admin\Resources\OrganizationResource\Pages;

use App\Actions\Admin\OrganizationVerification;
use App\Enums\OrganizationVerificationState;
use App\Filament\Admin\Resources\OrganizationResource;
use App\Helpers\Util;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewOrganization extends ViewRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verify')
                ->label(__('actions/admin.organization-verification.verify.label'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->verification_state === OrganizationVerificationState::PENDING)
                ->requiresConfirmation()
                ->action(function () {
                    Util::run(function () {
                        OrganizationVerification::run($this->record, [
                            'decision' => OrganizationVerificationState::VERIFIED->value,
                        ]);
                        Util::filamentNotification(__('actions/admin.organization-verification.verify.success'));
                        $this->refreshFormData(['verification_state', 'verification_by', 'verified_at', 'is_active']);
                    });
                }),

            Actions\Action::make('suspend')
                ->label(__('actions/admin.organization-verification.suspend.label'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->verification_state, [
                    OrganizationVerificationState::PENDING,
                    OrganizationVerificationState::VERIFIED,
                ]))
                ->form([
                    Forms\Components\Textarea::make('verification_reason')
                        ->required()
                        ->label(__('actions/admin.organization-verification.form.verification_reason'))
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    Util::run(function () use ($data) {
                        OrganizationVerification::run($this->record, [
                            'decision' => OrganizationVerificationState::SUSPENDED->value,
                            'verification_reason' => $data['verification_reason'],
                        ]);
                        Util::filamentNotification(__('actions/admin.organization-verification.suspend.success'));
                        $this->refreshFormData(['verification_state', 'verification_by', 'verified_at', 'verification_reason', 'is_active']);
                    });
                }),
        ];
    }
}
