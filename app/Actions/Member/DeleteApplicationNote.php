<?php

namespace App\Actions\Member;

use App\Helpers\Util;
use App\Models\ApplicationNote;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteApplicationNote
{
    use AsAction;

    public function handle(ApplicationNote $note): void
    {
        Util::getActivityLog('application-note.delete')
            ->performedOn($note)
            ->withProperties([
                'ip' => request()->ip(),
                'application_id' => $note->application_id,
                'body_length' => mb_strlen($note->body),
            ])
            ->log('Nota interna eliminada');

        $note->delete();
    }
}
