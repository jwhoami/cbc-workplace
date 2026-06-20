<?php

declare(strict_types=1);

namespace App\Filament\Member\Resources\JobAlertResource\Pages;

use App\Actions\Member\CreateJobAlertAction;
use App\Exceptions\AlertQuotaExceededException;
use App\Exceptions\DuplicateAlertException;
use App\Filament\Member\Resources\JobAlertResource;
use App\Helpers\Util;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateJobAlert extends CreateRecord
{
    protected static string $resource = JobAlertResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $member = auth('member')->user();

        try {
            return CreateJobAlertAction::run($member, $data);
        } catch (AlertQuotaExceededException|DuplicateAlertException $e) {
            Util::filamentNotification($e->getMessage(), 'danger');
            throw new Halt;
        }
    }
}
