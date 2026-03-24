<?php

namespace App\Actions\Member;

use App\Models\Venture;
use Lorisleiva\Actions\Concerns\AsAction;

class Duplicate
{
    use AsAction;

    public function handle(Venture $record)
    {
        $new = $record->replicate([
            'approval_state',
            'approval_by',
            'approval_at',
            'approval_reason',
        ]);
        $new->title = "{$record->title} (Copy)";
        $new->save();

        return $new;
    }
}
