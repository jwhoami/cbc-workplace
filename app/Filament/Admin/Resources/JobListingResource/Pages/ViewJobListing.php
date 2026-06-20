<?php

namespace App\Filament\Admin\Resources\JobListingResource\Pages;

use App\Actions\Admin\JobListingApproval;
use App\Enums\JobListingState;
use App\Filament\Admin\Resources\JobListingResource;
use App\Helpers\Util;
use App\Models\JobListing;
use Filament\Actions;
use Filament\Forms;
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
        return [
            Actions\Action::make('approve-reject-job-listing')
                ->label(__('common.actions.decision.label'))
                ->modalWidth('md')
                ->visible(fn (JobListing $record) => $record->state === JobListingState::PENDING)
                ->action(function (JobListing $record, array $data) {
                    Util::run(fn () => JobListingApproval::run($record, $data));
                    $this->redirect(JobListingResource::getUrl('view', ['record' => $record]));
                })
                ->form([
                    Forms\Components\Radio::make('decision')
                        ->label(__('common.actions.decision.label'))
                        ->required()
                        ->inline()
                        ->inlineLabel(false)
                        ->options([
                            JobListingState::ACTIVE->value => JobListingState::ACTIVE->getLabel(),
                            JobListingState::REJECTED->value => JobListingState::REJECTED->getLabel(),
                        ]),
                    Forms\Components\Textarea::make('approval_reason')
                        ->label(__('models/job-listing.fields.approval_reason'))
                        ->requiredIf('decision', JobListingState::REJECTED->value),
                ]),
        ];
    }
}
