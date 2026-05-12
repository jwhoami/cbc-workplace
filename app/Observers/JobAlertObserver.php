<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helpers\DiacriticFolder;
use App\Models\JobAlert;

class JobAlertObserver
{
    public function saving(JobAlert $alert): void
    {
        $alert->city_folded = $alert->city !== null && $alert->city !== ''
            ? DiacriticFolder::fold((string) $alert->city)
            : null;
    }
}
