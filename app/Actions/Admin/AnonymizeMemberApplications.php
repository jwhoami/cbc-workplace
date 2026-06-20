<?php

namespace App\Actions\Admin;

use App\Helpers\Util;
use App\Models\Application;
use App\Models\Member;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class AnonymizeMemberApplications
{
    use AsAction;

    public function handle(Member $member): int
    {
        $applications = Application::query()
            ->where('member_id', $member->id)
            ->whereNull('anonymized_at')
            ->get();

        if ($applications->isEmpty()) {
            return 0;
        }

        $disk = Storage::disk('public');
        $count = 0;

        foreach ($applications as $application) {
            if ($application->cv_snapshot_path && $disk->exists($application->cv_snapshot_path)) {
                $disk->delete($application->cv_snapshot_path);
            }

            $application->forceFill([
                'candidate_name_snapshot' => __('models/application.snapshot.anonymized_name'),
                'candidate_email_snapshot' => null,
                'cv_snapshot_path' => null,
                'cv_snapshot_filename' => null,
                'candidate_profile_id' => null,
                'member_id' => null,
                'anonymized_at' => now(),
            ])->save();

            Util::getActivityLog('application.anonymize')
                ->performedOn($application)
                ->withProperties([
                    'ip' => request()->ip(),
                    'former_member_id' => $member->id,
                ])
                ->log('Postulación anonimizada');

            $count++;
        }

        return $count;
    }
}
