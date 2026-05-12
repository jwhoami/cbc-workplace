<?php

declare(strict_types=1);

namespace App\Filament\Member\Resources\JobAlertResource\Pages;

use App\Actions\Member\UpdateJobAlertAction;
use App\Exceptions\DuplicateAlertException;
use App\Filament\Member\Resources\JobAlertResource;
use App\Helpers\Util;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditJobAlert extends EditRecord
{
    protected static string $resource = JobAlertResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return UpdateJobAlertAction::run($record, $data);
        } catch (DuplicateAlertException $e) {
            Util::filamentNotification($e->getMessage(), 'danger');
            throw new Halt;
        }
    }
}
