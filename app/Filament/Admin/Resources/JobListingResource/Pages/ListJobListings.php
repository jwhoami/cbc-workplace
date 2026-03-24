<?php

namespace App\Filament\Admin\Resources\JobListingResource\Pages;

use App\Filament\Admin\Resources\JobListingResource;
use Filament\Resources\Pages\ListRecords;

class ListJobListings extends ListRecords
{
    protected static string $resource = JobListingResource::class;
}
