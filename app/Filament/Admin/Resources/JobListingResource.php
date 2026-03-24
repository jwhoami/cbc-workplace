<?php

namespace App\Filament\Admin\Resources;

use App\Enums\JobListingState;
use App\Filament\Admin\Resources\JobListingResource\Pages;
use App\Filament\Shared\Resources\BaseJobListingResource;
use Filament\Tables;
use Filament\Tables\Table;

class JobListingResource extends BaseJobListingResource
{
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobListings::route('/'),
            'view' => Pages\ViewJobListing::route('/{record}'),
        ];
    }

    public static function table(Table $table): Table
    {
        $table = parent::table($table);

        return $table
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label(__('models/job-listing.fields.state'))
                    ->options(JobListingState::class),
                Tables\Filters\SelectFilter::make('organization_id')
                    ->label(__('models/job-listing.fields.organization'))
                    ->relationship('organization', 'display_name'),
            ]);
    }
}
