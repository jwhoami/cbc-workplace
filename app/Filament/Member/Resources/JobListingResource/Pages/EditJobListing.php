<?php

namespace App\Filament\Member\Resources\JobListingResource\Pages;

use App\Actions\Member\RequestJobListingApproval;
use App\Enums\JobListingState;
use App\Filament\Member\Resources\JobListingResource;
use App\Helpers\Util;
use App\Models\JobListing;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

class EditJobListing extends EditRecord
{
    protected static string $resource = JobListingResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record->canEdit()) {
            Util::filamentNotification(__('models/job-listing.notifications.cannot_edit'), 'danger');
            $this->redirect(JobListingResource::getUrl('view', ['record' => $this->record]));
        }
    }

    public function form(Form $form): Form
    {
        $form = parent::form($form);

        if ($this->record->state === JobListingState::REJECTED && $this->record->approval_reason) {
            $banner = Forms\Components\Placeholder::make('rejection_banner')
                ->hiddenLabel()
                ->content(new HtmlString(
                    '<div class="rounded-lg bg-danger-50 p-4 text-danger-700 dark:bg-danger-950 dark:text-danger-400">'
                    .'<strong>'.__('models/job-listing.rejection.banner').'</strong><br>'
                    .'<strong>'.__('models/job-listing.rejection.reason_label').':</strong> '
                    .e($this->record->approval_reason)
                    .'</div>'
                ));

            $schema = $form->getComponents(true);
            array_unshift($schema, $banner);
            $form->schema($schema);
        }

        return $form;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label(__('models/job-listing.actions.preview'))
                ->icon('heroicon-o-eye')
                ->modalContent(fn (JobListing $record) => view('filament.member.pages.job-listing-preview', ['record' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('common.actions.back.label')),
            Actions\Action::make('submit-for-approval')
                ->label(__('models/job-listing.actions.submit_for_approval'))
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->visible(fn (JobListing $record) => $record->canSubmit())
                ->action(function (JobListing $record) {
                    Util::run(fn () => RequestJobListingApproval::run($record));
                    Util::filamentNotification(__('models/job-listing.notifications.submitted'));
                    $this->redirect(JobListingResource::getUrl('view', ['record' => $record]));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
