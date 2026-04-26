<?php

namespace App\Filament\Member\Pages;

use App\Actions\Member\SubmitApplication;
use App\Enums\JobListingState;
use App\Helpers\Util;
use App\Models\JobListing;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class ApplyToJobListing extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.member.pages.apply-to-job-listing';

    protected static ?string $slug = 'apply/{record}';

    public ?array $data = [];

    public ?JobListing $record = null;

    public function mount(string $record): void
    {
        $this->record = JobListing::query()
            ->where('slug', $record)
            ->orWhere('id', $record)
            ->firstOrFail();

        $this->data = [
            'cover_letter' => null,
            'screening_answers' => $this->initialScreeningAnswers(),
        ];

        $this->form->fill($this->data);
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('models/application.form.submit').' — '.$this->record?->title;
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make($this->record?->title ?? '')
                    ->description(fn () => $this->record?->organization?->display_name)
                    ->schema([
                        Forms\Components\Textarea::make('cover_letter')
                            ->label(__('models/application.fields.cover_letter'))
                            ->placeholder(__('models/application.form.cover_letter_placeholder'))
                            ->maxLength(2000)
                            ->rows(8)
                            ->validationMessages([
                                'max.string' => __('models/application.validation.cover_letter_max'),
                            ]),
                        Forms\Components\Repeater::make('screening_answers')
                            ->label(__('models/application.fields.screening_answers'))
                            ->visible(fn () => ! empty($this->record?->screening_questions))
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->disableItemMovement()
                            ->schema([
                                Forms\Components\Hidden::make('question'),
                                Forms\Components\Placeholder::make('question_label')
                                    ->label(__('models/application.fields.screening_question'))
                                    ->content(fn ($get) => $get('question')),
                                Forms\Components\Textarea::make('answer')
                                    ->label(__('models/application.fields.screening_answer'))
                                    ->required()
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->validationMessages([
                                        'max.string' => __('models/application.validation.answer_max'),
                                        'required' => __('models/application.validation.answer_required'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function submit(): mixed
    {
        $member = $this->getMember();

        if (! $member instanceof Member) {
            Util::filamentNotification(__('models/application.notifications.no_profile'), 'danger');

            return null;
        }

        $data = $this->form->getState();

        try {
            SubmitApplication::run($member, $this->record, [
                'cover_letter' => $data['cover_letter'] ?? null,
                'screening_answers' => $data['screening_answers'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Util::filamentNotification($e->getMessage(), 'danger');

            return null;
        }

        Util::filamentNotification(__('models/application.notifications.created'));

        return redirect()->to('/member/applications');
    }

    public static function canAccess(): bool
    {
        $user = auth('member')->user();
        if (! $user instanceof Member) {
            return false;
        }

        return $user->candidateProfile()->exists();
    }

    protected function getMember(): ?Member
    {
        $user = auth('member')->user();

        return $user instanceof Member ? $user : null;
    }

    protected function initialScreeningAnswers(): array
    {
        return collect($this->record?->screening_questions ?? [])
            ->map(fn ($q) => [
                'question' => is_array($q) ? ($q['question'] ?? '') : $q,
                'answer' => '',
            ])
            ->values()
            ->all();
    }

    protected function getViewData(): array
    {
        return [
            'listing' => $this->record,
            'isSubmittable' => $this->record?->state === JobListingState::ACTIVE
                && (! $this->record?->application_deadline || ! $this->record->application_deadline->isPast()),
        ];
    }
}
