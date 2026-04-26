<?php

namespace App\Actions\Member;

use App\Enums\ApplicationStatus;
use App\Helpers\Util;
use App\Mail\Member\ApplicationStatusChanged;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateApplicationStatus
{
    use AsAction;

    public function handle(Application $application, ApplicationStatus $next): void
    {
        $previous = $application->status;

        if (! $previous->canTransitionTo($next)) {
            throw new \Exception(__('models/application.notifications.invalid_transition'));
        }

        $application->status = $next;
        $application->last_status_changed_at = now();
        $application->last_status_changed_by = auth()->user()?->name ?? 'Sistema';
        $application->save();

        $application->addComment(__('models/application.comments.status_changed', [
            'from' => $previous->getLabel(),
            'to' => $next->getLabel(),
        ]));

        Util::getActivityLog('application.status-change')
            ->performedOn($application)
            ->withProperties([
                'ip' => request()->ip(),
                'from' => $previous->value,
                'to' => $next->value,
            ])
            ->log('Cambio de estado de postulación');

        Mail::to($application->member)->send(
            new ApplicationStatusChanged($application, $previous, $next)
        );
    }
}
