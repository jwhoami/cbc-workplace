<?php

namespace App\Filament\Member\Resources\JobListingResource\Pages;

use App\Actions\Member\CloseJobListing;
use App\Actions\Member\RequestJobListingApproval;
use App\Enums\JobListingState;
use App\Filament\Member\Resources\JobListingResource;
use App\Helpers\Util;
use App\Models\JobListing;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJobListing extends ViewRecord
{
    protected static string $resource = JobListingResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record->updateViewCount();
    }

    protected function getHeaderActions(): array
    {
        $frozen = fn (): bool => auth('member')->user()?->organization?->is_suspended() ?? false;

        return [
            Actions\EditAction::make()
                ->visible(fn (JobListing $record) => $record->canEdit() && ! $frozen()),
            Actions\Action::make('submit-for-approval')
                ->label(__('models/job-listing.actions.submit_for_approval'))
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->visible(fn (JobListing $record) => $record->canSubmit() && ! $frozen())
                ->action(function (JobListing $record) {
                    Util::run(fn () => RequestJobListingApproval::run($record));
                    Util::filamentNotification(__('models/job-listing.notifications.submitted'));
                    $this->redirect(JobListingResource::getUrl('view', ['record' => $record]));
                }),
            Actions\Action::make('close-job-listing')
                ->label(__('models/job-listing.actions.close'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (JobListing $record) => $record->state === JobListingState::ACTIVE && ! $frozen())
                ->action(function (JobListing $record) {
                    Util::run(fn () => CloseJobListing::run($record));
                    Util::filamentNotification(__('models/job-listing.notifications.closed'));
                    $this->redirect(JobListingResource::getUrl('view', ['record' => $record]));
                }),
        ];
    }
}
