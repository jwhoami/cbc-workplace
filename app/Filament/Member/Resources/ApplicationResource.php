<?php

namespace App\Filament\Member\Resources;

use App\Enums\ApplicationStatus;
use App\Filament\Member\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static bool $shouldSkipAuthorization = true;

    public static function getNavigationLabel(): string
    {
        return __('models/application.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models/application.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('models/application.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models/application.plural-label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('member_id', auth('member')->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jobListing.title')
                    ->label(__('models/application.fields.job_listing'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('jobListing.organization.display_name')
                    ->label(__('models/application.fields.organization'))
                    ->limit(40),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('models/application.fields.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label(__('models/application.fields.submitted_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateHeading(__('models/application.plural-label'))
            ->emptyStateDescription('—');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('jobListing.title')
                            ->label(__('models/application.fields.job_listing')),
                        Infolists\Components\TextEntry::make('jobListing.organization.display_name')
                            ->label(__('models/application.fields.organization')),
                        Infolists\Components\TextEntry::make('status')
                            ->label(__('models/application.fields.status'))
                            ->badge(),
                        Infolists\Components\TextEntry::make('submitted_at')
                            ->label(__('models/application.fields.submitted_at'))
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('cover_letter')
                            ->label(__('models/application.fields.cover_letter'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('cv_snapshot_filename')
                            ->label(__('models/application.fields.cv_snapshot'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'view' => Pages\ViewApplication::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
