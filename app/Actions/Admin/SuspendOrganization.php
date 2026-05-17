<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\JobListingState;
use App\Helpers\Util;
use App\Mail\Organization\Suspended;
use App\Models\Comments;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SuspendOrganization
{
    use AsAction;

    public function handle(Organization $organization, ?string $reason = null): SuspendOrganizationResult
    {
        $reason = $this->normalizeReason($reason);

        if ($organization->is_suspended()) {
            return SuspendOrganizationResult::alreadySuspended();
        }

        $offersDeactivated = DB::transaction(function () use ($organization, $reason): int {
            $organization->suspended_at = now();
            $organization->suspended_by = Filament::auth()->user()?->name
                ?? auth()->user()?->name
                ?? 'Sistema';
            $organization->suspension_reason = $reason;
            $organization->save();

            $cascadeStart = now()->subSeconds(5);

            $count = JobListing::query()
                ->where('organization_id', $organization->id)
                ->where('state', JobListingState::ACTIVE)
                ->update([
                    'state' => JobListingState::CLOSED,
                    'closed_at' => now(),
                ]);

            if ($count > 0) {
                $offerIds = JobListing::query()
                    ->where('organization_id', $organization->id)
                    ->where('state', JobListingState::CLOSED)
                    ->where('closed_at', '>=', $cascadeStart)
                    ->pluck('id');

                foreach ($offerIds as $offerId) {
                    Comments::create([
                        'commentable_type' => JobListing::class,
                        'commentable_id' => $offerId,
                        'comment' => __('actions/admin.suspend-organization.offer-deactivated-comment'),
                        'comment_by' => 'Sistema',
                    ]);
                }
            }

            $organization->addComment(__('actions/admin.suspend-organization.org-comment'));

            return $count;
        });

        Util::getActivityLog('organization-suspended')
            ->performedOn($organization)
            ->withProperties([
                'ip' => request()->ip(),
                'organization_id' => $organization->id,
                'offers_deactivated' => $offersDeactivated,
                'suspension_reason' => $reason,
            ])
            ->log(__('actions/admin.suspend-organization.log-message'));

        $admins = $this->adminMembersFor($organization);
        $enqueued = 0;

        foreach ($admins as $adminMember) {
            try {
                Mail::to($adminMember)->queue(new Suspended($organization));

                Util::getActivityLog('mail-suspension-dispatch-enqueued')
                    ->performedOn($organization)
                    ->withProperties(['recipient' => $adminMember->email])
                    ->log(__('actions/admin.suspend-organization.mail-enqueued-log'));

                $enqueued++;
            } catch (\Throwable $e) {
                Util::getActivityLog('mail-suspension-dispatch-failed')
                    ->performedOn($organization)
                    ->withProperties([
                        'recipient' => $adminMember->email,
                        'exception_class' => $e::class,
                    ])
                    ->log(__('actions/admin.suspend-organization.mail-failed-log'));
            }
        }

        return SuspendOrganizationResult::suspended(
            offersDeactivated: $offersDeactivated,
            notificationsEnqueued: $enqueued,
        );
    }

    /**
     * Singleton today; ready for a multi-admin pivot tomorrow (see research.md §R2).
     *
     * @return Collection<int, Member>
     */
    protected function adminMembersFor(Organization $organization): Collection
    {
        return collect($organization->member ? [$organization->member] : []);
    }

    private function normalizeReason(?string $reason): ?string
    {
        if ($reason === null) {
            return null;
        }

        $reason = trim($reason);

        return $reason === '' ? null : $reason;
    }
}
