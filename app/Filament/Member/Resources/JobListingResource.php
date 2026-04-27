<?php

namespace App\Filament\Member\Resources;

use App\Actions\Member\CloseJobListing;
use App\Enums\JobListingState;
use App\Filament\Member\Resources\JobListingResource\Pages;
use App\Filament\Member\Resources\JobListingResource\RelationManagers\ApplicationsRelationManager;
use App\Filament\Shared\Resources\BaseJobListingResource;
use App\Helpers\Util;
use App\Models\JobListing;
use App\Models\Organization;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobListingResource extends BaseJobListingResource
{
    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('close-job-listing')
                    ->label(__('models/job-listing.actions.close'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (JobListing $record) => $record->state === JobListingState::ACTIVE)
                    ->action(function (JobListing $record) {
                        Util::run(fn () => CloseJobListing::run($record));
                        Util::filamentNotification(__('models/job-listing.notifications.closed'));
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobListings::route('/'),
            'create' => Pages\CreateJobListing::route('/create'),
            'view' => Pages\ViewJobListing::route('/{record}'),
            'edit' => Pages\EditJobListing::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $organization = Organization::where('member_id', auth('member')->id())->first();

        if ($organization) {
            return parent::getEloquentQuery()->where('organization_id', $organization->id);
        }

        return parent::getEloquentQuery()->where('member_id', auth('member')->id());
    }

    public static function getRelations(): array
    {
        return [
            ApplicationsRelationManager::class,
        ];
    }
}
