<?php

declare(strict_types=1);

namespace App\Filament\Member\Resources\JobAlertResource\Pages;

use App\Filament\Member\Resources\JobAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobAlerts extends ListRecords
{
    protected static string $resource = JobAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('models/job-alert.actions.create')),
        ];
    }
}
