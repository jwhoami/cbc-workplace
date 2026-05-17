<?php

namespace App\Filament\Member\Resources\JobListingResource\RelationManagers;

use App\Actions\Member\AddApplicationNote;
use App\Actions\Member\UpdateApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Helpers\Util;
use App\Models\Application;
use App\Models\JobListing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'Postulaciones';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof JobListing
            && auth('member')->id() === $ownerRecord->member_id;
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('candidate_name_snapshot')
            ->columns([
                Tables\Columns\TextColumn::make('candidate_name_snapshot')
                    ->label(__('models/application.fields.candidate_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('models/application.fields.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label(__('models/application.fields.submitted_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_status_changed_at')
                    ->label(__('models/application.fields.last_status_changed_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('models/application.fields.status'))
                    ->options(collect(ApplicationStatus::cases())->mapWithKeys(
                        fn ($s) => [$s->value => $s->getLabel()]
                    )->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn (Application $record) => $record->candidate_name_snapshot)
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('candidate_name_snapshot')
                            ->label(__('models/application.fields.candidate_name')),
                        \Filament\Infolists\Components\TextEntry::make('candidate_email_snapshot')
                            ->label(__('models/application.fields.candidate_email')),
                        \Filament\Infolists\Components\TextEntry::make('cover_letter')
                            ->label(__('models/application.fields.cover_letter'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        \Filament\Infolists\Components\TextEntry::make('cv_snapshot_filename')
                            ->label(__('models/application.fields.cv_snapshot'))
                            ->placeholder('—'),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->label(__('models/application.fields.status'))
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('submitted_at')
                            ->label(__('models/application.fields.submitted_at'))
                            ->dateTime('d/m/Y H:i'),
                    ]),
                Tables\Actions\Action::make('changeStatus')
                    ->label(__('common.actions.status-set.label'))
                    ->icon('heroicon-o-arrow-right')
                    ->visible(fn (Application $record) => ! $record->status->isTerminal()
                        && ! (auth('member')->user()?->organization?->is_suspended() ?? false))
                    ->form(fn (Application $record) => [
                        Forms\Components\Select::make('next')
                            ->label(__('models/application.fields.status'))
                            ->options(
                                collect(ApplicationStatus::cases())
                                    ->filter(fn ($s) => $record->status->canTransitionTo($s))
                                    ->mapWithKeys(fn ($s) => [$s->value => $s->getLabel()])
                                    ->all()
                            )
                            ->required(),
                    ])
                    ->action(function (Application $record, array $data) {
                        Util::run(fn () => UpdateApplicationStatus::run(
                            $record,
                            ApplicationStatus::from((int) $data['next'])
                        ));
                        Util::filamentNotification(__('models/application.notifications.status_changed'));
                    }),
                Tables\Actions\Action::make('addNote')
                    ->label(__('models/application-note.actions.add'))
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn () => ! (auth('member')->user()?->organization?->is_suspended() ?? false))
                    ->form([
                        Forms\Components\Textarea::make('body')
                            ->label(__('models/application-note.fields.body'))
                            ->placeholder(__('models/application-note.form.body_placeholder'))
                            ->required()
                            ->maxLength(2000)
                            ->rows(5),
                    ])
                    ->action(function (Application $record, array $data) {
                        Util::run(fn () => AddApplicationNote::run($record, $data['body']));
                        Util::filamentNotification(__('models/application-note.notifications.created'));
                    }),
                Tables\Actions\Action::make('viewNotes')
                    ->label(__('models/application-note.plural-label'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->modalHeading(fn (Application $record) => __('models/application-note.plural-label').' — '.$record->candidate_name_snapshot)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('common.actions.back.label'))
                    ->form(fn (Application $record) => [
                        Forms\Components\Repeater::make('notes')
                            ->hiddenLabel()
                            ->default(fn () => $record->notes()->orderBy('created_at')->get()->map(fn ($n) => [
                                'id' => $n->id,
                                'author_name_snapshot' => $n->author_name_snapshot,
                                'created_at' => $n->created_at?->format('d/m/Y H:i'),
                                'body' => $n->body,
                            ])->all())
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->disableItemMovement()
                            ->schema([
                                Forms\Components\Hidden::make('id'),
                                Forms\Components\TextInput::make('author_name_snapshot')
                                    ->label(__('models/application-note.fields.author'))
                                    ->disabled(),
                                Forms\Components\TextInput::make('created_at')
                                    ->label(__('models/application-note.fields.created_at'))
                                    ->disabled(),
                                Forms\Components\Textarea::make('body')
                                    ->label(__('models/application-note.fields.body'))
                                    ->disabled()
                                    ->rows(3),
                            ]),
                    ]),
            ])
            ->bulkActions([])
            ->emptyStateHeading(__('models/application.plural-label'))
            ->emptyStateDescription('—');
    }
}
