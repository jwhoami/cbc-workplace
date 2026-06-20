<?php

namespace App\Actions\Member;

use App\Helpers\Util;
use App\Models\Venture;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class ExtendValidity
{
    use AsAction;

    public function handle(Venture $venture, Carbon $date)
    {
        if ($venture->is_expired) {
            Util::filamentNotification('Este empredimiento aún ha vencido', 'warning');

            return;
        }
        $venture->expires_at = $date;
        $venture->save();

        $venture->addComment(__('Emprendimiento extendido hasta :0', [$venture->expires_at->format('Y-m-d')]));
    }
}
