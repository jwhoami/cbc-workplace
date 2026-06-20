<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\OrganizationResource\Pages;

use App\Actions\Admin\OrganizationVerification;
use App\Actions\Admin\ReactivateOrganization;
use App\Actions\Admin\SuspendOrganization;
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

            Actions\Action::make('suspend-organization')
                ->label(__('actions/admin.suspend-organization.label'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->canBeSuspended()
                    && (auth()->user()?->can('suspend', $this->record) ?? false))
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label(__('actions/admin.suspend-organization.form.reason'))
                        ->maxLength(500)
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    Util::run(function () use ($data) {
                        $result = SuspendOrganization::run($this->record, $data['reason'] ?? null);

                        if ($result->wasAlreadySuspended()) {
                            Util::filamentNotification(
                                __('actions/admin.suspend-organization.notification.already-suspended'),
                                'warning',
                            );
                        } else {
                            Util::filamentNotification(__('actions/admin.suspend-organization.notification.success', [
                                'count' => $result->offersDeactivated,
                            ]));
                        }

                        $this->refreshFormData([
                            'suspended_at', 'suspended_by', 'suspension_reason',
                            'verification_state', 'is_active',
                        ]);
                    });
                }),

            Actions\Action::make('reactivate-organization')
                ->label(__('actions/admin.reactivate-organization.label'))
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->visible(fn () => $this->record->canBeReactivated()
                    && (auth()->user()?->can('reactivate', $this->record) ?? false))
                ->requiresConfirmation()
                ->action(function () {
                    Util::run(function () {
                        $result = ReactivateOrganization::run($this->record);

                        if ($result->wasNotSuspended()) {
                            Util::filamentNotification(
                                __('actions/admin.reactivate-organization.notification.not-suspended'),
                                'warning',
                            );
                        } else {
                            Util::filamentNotification(__('actions/admin.reactivate-organization.notification.success'));
                        }

                        $this->refreshFormData([
                            'suspended_at', 'suspended_by', 'suspension_reason',
                            'verification_state', 'is_active',
                        ]);
                    });
                }),
        ];
    }
}
