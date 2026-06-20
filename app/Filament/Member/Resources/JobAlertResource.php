<?php

declare(strict_types=1);

namespace App\Filament\Member\Resources;

use App\Actions\Member\DeleteJobAlertAction;
use App\Actions\Member\ToggleJobAlertAction;
use App\Enums\JobAlertFrequency;
use App\Filament\Member\Resources\JobAlertResource\Pages;
use App\Helpers\Util;
use App\Models\Category;
use App\Models\JobAlert;
use App\Rules\SingleLineCity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobAlertResource extends Resource
{
    protected static ?string $model = JobAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static bool $shouldSkipAuthorization = true;

    public static function getNavigationLabel(): string
    {
        return __('models/job-alert.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models/job-alert.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('models/job-alert.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models/job-alert.plural-label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('member_id', auth('member')->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('models/job-alert.fields.category'))
                    ->placeholder(__('models/job-alert.form.category_placeholder'))
                    ->options(fn () => Category::query()
                        ->where('scope', 'JobListing')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
                Forms\Components\TextInput::make('city')
                    ->label(__('models/job-alert.fields.city'))
                    ->placeholder(__('models/job-alert.form.city_placeholder'))
                    ->maxLength(80)
                    ->rules([new SingleLineCity]),
                Forms\Components\Select::make('frequency')
                    ->label(__('models/job-alert.fields.frequency'))
                    ->required()
                    ->options(collect(JobAlertFrequency::cases())
                        ->mapWithKeys(fn (JobAlertFrequency $f) => [$f->value => $f->getLabel()])
                        ->all()),
                Forms\Components\Toggle::make('active')
                    ->label(__('models/job-alert.fields.active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('models/job-alert.fields.category'))
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('models/job-alert.fields.city'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('frequency')
                    ->label(__('models/job-alert.fields.frequency'))
                    ->badge(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('models/job-alert.fields.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('models/job-alert.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle-active')
                    ->label(fn (JobAlert $record) => $record->active
                        ? __('models/job-alert.actions.toggle_inactive')
                        : __('models/job-alert.actions.toggle_active'))
                    ->icon(fn (JobAlert $record) => $record->active ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn (JobAlert $record) => $record->active ? 'warning' : 'success')
                    ->action(function (JobAlert $record) {
                        Util::run(fn () => ToggleJobAlertAction::run($record));
                        Util::filamentNotification(
                            $record->fresh()->active
                                ? __('models/job-alert.notifications.toggled_active')
                                : __('models/job-alert.notifications.toggled_inactive')
                        );
                    }),
                Tables\Actions\DeleteAction::make()
                    ->using(function (JobAlert $record) {
                        DeleteJobAlertAction::run($record);

                        return true;
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading(__('models/job-alert.plural-label'))
            ->emptyStateDescription('—');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobAlerts::route('/'),
            'create' => Pages\CreateJobAlert::route('/create'),
            'edit' => Pages\EditJobAlert::route('/{record}/edit'),
        ];
    }
}
