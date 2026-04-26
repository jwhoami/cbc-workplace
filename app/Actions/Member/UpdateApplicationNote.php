<?php

namespace App\Actions\Member;

use App\Helpers\Util;
use App\Models\ApplicationNote;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateApplicationNote
{
    use AsAction;

    public function handle(ApplicationNote $note, string $body): void
    {
        $body = trim($body);
        if ($body === '') {
            throw new \Exception(__('models/application-note.validation.body_required'));
        }
        if (mb_strlen($body) > 2000) {
            throw new \Exception(__('models/application-note.validation.body_max'));
        }

        $previousLength = mb_strlen($note->body);
        $note->body = $body;
        $note->save();

        Util::getActivityLog('application-note.update')
            ->performedOn($note)
            ->withProperties([
                'ip' => request()->ip(),
                'application_id' => $note->application_id,
                'previous_body_length' => $previousLength,
                'new_body_length' => mb_strlen($body),
            ])
            ->log('Nota interna actualizada');
    }
}
