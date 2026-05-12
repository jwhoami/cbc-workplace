<?php

namespace App\Actions\Member;

use App\Helpers\Util;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\Member;
use Lorisleiva\Actions\Concerns\AsAction;

class AddApplicationNote
{
    use AsAction;

    public function handle(Application $application, string $body): ApplicationNote
    {
        $body = trim($body);
        if ($body === '') {
            throw new \Exception(__('models/application-note.validation.body_required'));
        }
        if (mb_strlen($body) > 2000) {
            throw new \Exception(__('models/application-note.validation.body_max'));
        }

        $author = auth()->user();

        $note = (new ApplicationNote)->forceFill([
            'application_id' => $application->id,
            'author_user_id' => $author instanceof Member ? null : $author?->id,
            'author_name_snapshot' => $author?->name ?? 'Sistema',
            'body' => $body,
        ]);
        $note->save();

        Util::getActivityLog('application-note.create')
            ->performedOn($note)
            ->withProperties([
                'ip' => request()->ip(),
                'application_id' => $application->id,
            ])
            ->log('Nota interna agregada');

        return $note;
    }
}
