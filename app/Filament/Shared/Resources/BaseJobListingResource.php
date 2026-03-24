<?php

namespace App\Filament\Shared\Resources;

use App\Enums\ContractType;
use App\Enums\JobListingState;
use App\Enums\WorkModality;
use App\Helpers\Util;
use App\Models\JobListing;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BaseJobListingResource extends Resource
{
    protected static ?string $model = JobListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function getModelLabel(): string
    {
        return __('models/job-listing.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models/job-listing.plural-label');
    }

    public static function getNavigationLabel(): string
    {
        return __('models/job-listing.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models/job-listing.navigation.group');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make()
                    ->columns(['md' => 3, 'lg' => 3])
                    ->schema([
                        Infolists\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Infolists\Components\Section::make(__('models/job-listing.sections.basic'))
                                    ->columns(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->label(__('models/job-listing.fields.title'))
                                            ->columnSpanFull(),
                                        Infolists\Components\TextEntry::make('contract_type')
                                            ->label(__('models/job-listing.fields.contract_type')),
                                        Infolists\Components\TextEntry::make('work_modality')
                                            ->label(__('models/job-listing.fields.work_modality')),
                                        Infolists\Components\TextEntry::make('application_deadline')
                                            ->label(__('models/job-listing.fields.application_deadline'))
                                            ->date(config('appx.dateTimeFormat.display.date')),
                                        Infolists\Components\TextEntry::make('view_count')
                                            ->label(__('models/job-listing.fields.view_count')),
                                    ]),
                                Infolists\Components\Section::make(__('models/job-listing.sections.details'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('description')
                                            ->label(__('models/job-listing.fields.description'))
                                            ->html()
                                            ->columnSpanFull(),
                                        Infolists\Components\TextEntry::make('requirements')
                                            ->label(__('models/job-listing.fields.requirements'))
                                            ->html()
                                            ->columnSpanFull(),
                                    ]),
                                Infolists\Components\Section::make(__('models/job-listing.sections.screening'))
                                    ->visible(fn (JobListing $record) => ! empty($record->screening_questions))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('screening_questions')
                                            ->label(false)
                                            ->listWithLineBreaks()
                                            ->bulleted(),
                                    ]),
                            ]),
                        Infolists\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Infolists\Components\Section::make(__('models/job-listing.sections.location'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('city')
                                            ->label(__('models/job-listing.fields.city')),
                                        Infolists\Components\TextEntry::make('province')
                                            ->label(__('models/job-listing.fields.province')),
                                    ]),
                                Infolists\Components\Section::make(__('models/job-listing.sections.salary'))
                                    ->visible(fn (JobListing $record) => $record->salary_min || $record->salary_max)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('salary_min')
                                            ->label(__('models/job-listing.fields.salary_min'))
                                            ->money(fn (JobListing $record) => $record->currency),
                                        Infolists\Components\TextEntry::make('salary_max')
                                            ->label(__('models/job-listing.fields.salary_max'))
                                            ->money(fn (JobListing $record) => $record->currency),
                                    ]),
                                Infolists\Components\Section::make(__('models/job-listing.sections.approval'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('state')
                                            ->label(__('models/job-listing.fields.state'))
                                            ->badge()
                                            ->color(fn (JobListingState $state) => match ($state) {
                                                JobListingState::DRAFT => 'gray',
                                                JobListingState::PENDING => 'warning',
                                                JobListingState::ACTIVE => 'success',
                                                JobListingState::REJECTED => 'danger',
                                                JobListingState::CLOSED => 'info',
                                                JobListingState::EXPIRED => 'gray',
                                            }),
                                        Infolists\Components\TextEntry::make('approval_by')
                                            ->label(__('models/job-listing.fields.approval_by'))
                                            ->visible(fn (JobListing $record) => $record->approval_by),
                                        Infolists\Components\TextEntry::make('approval_at')
                                            ->label(__('models/job-listing.fields.approval_at'))
                                            ->dateTime(config('appx.dateTimeFormat.display.dateTime'))
                                            ->visible(fn (JobListing $record) => $record->approval_at),
                                        Infolists\Components\TextEntry::make('approval_reason')
                                            ->label(__('models/job-listing.fields.approval_reason'))
                                            ->visible(fn (JobListing $record) => $record->approval_reason),
                                        Infolists\Components\TextEntry::make('published_at')
                                            ->label(__('models/job-listing.fields.published_at'))
                                            ->dateTime(config('appx.dateTimeFormat.display.dateTime'))
                                            ->visible(fn (JobListing $record) => $record->published_at),
                                        Infolists\Components\TextEntry::make('closed_at')
                                            ->label(__('models/job-listing.fields.closed_at'))
                                            ->dateTime(config('appx.dateTimeFormat.display.dateTime'))
                                            ->visible(fn (JobListing $record) => $record->closed_at),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('models/job-listing.sections.basic'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('models/job-listing.fields.title'))
                            ->placeholder(__('models/job-listing.form.placeholders.title'))
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),
                        SelectTree::make('category_id')
                            ->label(__('models/job-listing.fields.category'))
                            ->required()
                            ->relationship(
                                relationship: 'categories',
                                titleAttribute: 'name',
                                parentAttribute: 'parent_id',
                                modifyQueryUsing: fn (Builder $query) => $query->where('scope', 'JobListing')->orderBy('name', 'asc'),
                                modifyChildQueryUsing: fn (Builder $query) => $query->orderBy('name', 'asc'),
                            ),
                        Forms\Components\Select::make('contract_type')
                            ->label(__('models/job-listing.fields.contract_type'))
                            ->required()
                            ->options(ContractType::class),
                        Forms\Components\Select::make('work_modality')
                            ->label(__('models/job-listing.fields.work_modality'))
                            ->required()
                            ->options(WorkModality::class),
                        Forms\Components\DatePicker::make('application_deadline')
                            ->label(__('models/job-listing.fields.application_deadline'))
                            ->helperText(__('models/job-listing.form.helpers.application_deadline'))
                            ->required(),
                    ]),
                Forms\Components\Section::make(__('models/job-listing.sections.details'))
                    ->schema([
                    Forms\Components\RichEditor::make('description')
                            ->label(__('models/job-listing.fields.description'))
                            ->placeholder(__('models/job-listing.form.placeholders.description'))
                            ->required()
                            ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                            ->columnSpanFull(),
                    Forms\Components\RichEditor::make('requirements')
                            ->label(__('models/job-listing.fields.requirements'))
                            ->placeholder(__('models/job-listing.form.placeholders.requirements'))
                            ->required()
                            ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                            ->columnSpanFull(),
                ]),
                Forms\Components\Section::make(__('models/job-listing.sections.location'))
                    ->columns(2)
                    ->schema([
                    Forms\Components\TextInput::make('city')
                            ->label(__('models/job-listing.fields.city'))
                            ->placeholder(__('models/job-listing.form.placeholders.city'))
                            ->required()
                            ->maxLength(100),
                    Forms\Components\TextInput::make('province')
                            ->label(__('models/job-listing.fields.province'))
                            ->placeholder(__('models/job-listing.form.placeholders.province'))
                            ->required()
                            ->maxLength(100),
                ]),
                Forms\Components\Section::make(__('models/job-listing.sections.salary'))
                    ->columns(3)
                    ->schema([
                    Forms\Components\TextInput::make('salary_min')
                            ->label(__('models/job-listing.fields.salary_min'))
                            ->numeric()
                            ->minValue(0)
                            ->helperText(__('models/job-listing.form.helpers.salary')),
                    Forms\Components\TextInput::make('salary_max')
                            ->label(__('models/job-listing.fields.salary_max'))
                            ->numeric()
                            ->minValue(0)
                            ->gte('salary_min'),
                    Forms\Components\TextInput::make('currency')
                            ->label(__('models/job-listing.fields.currency'))
                            ->default('USD')
                            ->maxLength(3),
                ]),
                Forms\Components\Section::make(__('models/job-listing.sections.screening'))
                    ->schema([
                    Forms\Components\Repeater::make('screening_questions')
                            ->label(false)
                            ->helperText(__('models/job-listing.form.helpers.screening_questions'))
                            ->simple(
                                Forms\Components\TextInput::make('question')
                                    ->label(__('models/job-listing.fields.screening_question'))
                                    ->placeholder(__('models/job-listing.form.placeholders.screening_question'))
                                    ->required()
                                    ->maxLength(500),
                            )
                            ->maxItems(5)
                            ->reorderable()
                            ->defaultItems(0),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('models/job-listing.table.columns.title'))
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('organization.display_name')
                    ->label(__('models/job-listing.table.columns.organization'))
                    ->limit(25)
                    ->searchable()
                    ->hidden(fn () => Util::isPanelActive('member')),
                Tables\Columns\TextColumn::make('state')
                    ->label(__('models/job-listing.table.columns.state'))
                    ->badge()
                    ->color(fn (JobListingState $state) => match ($state) {
                        JobListingState::DRAFT => 'gray',
                        JobListingState::PENDING => 'warning',
                        JobListingState::ACTIVE => 'success',
                        JobListingState::REJECTED => 'danger',
                        JobListingState::CLOSED => 'info',
                        JobListingState::EXPIRED => 'gray',
                    }),
                Tables\Columns\TextColumn::make('contract_type')
                    ->label(__('models/job-listing.table.columns.contract_type'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('application_deadline')
                    ->label(__('models/job-listing.table.columns.application_deadline'))
                    ->date(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label(__('models/job-listing.table.columns.view_count'))
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('models/job-listing.table.columns.created_at'))
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            Tables\Filters\SelectFilter::make('state')
                    ->label(__('models/job-listing.fields.state'))
                    ->options(JobListingState::class),
        ])
            ->actions([
            Tables\Actions\ViewAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }
}
