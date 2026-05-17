<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobBoardStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    public function getColumnSpan(): string|int|array
    {
        return 'full';
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->isAdmin();
    }

    protected function getStats(): array
    {
        $orgStats = Organization::query()
            ->selectRaw('COUNT(*) AS total, SUM(verification_state = ?) AS verified', [
                OrganizationVerificationState::VERIFIED->value,
            ])
            ->first();

        $candidates = CandidateProfile::query()->count();
        $activeOffers = JobListing::query()->where('state', JobListingState::ACTIVE)->count();
        $recentApplications = Application::query()
            ->where('submitted_at', '>=', now()->subDay())
            ->count();

        return [
            Stat::make(
                __('widgets/admin/job-board.stats.candidates.label'),
                (string) $candidates,
            )->description(__('widgets/admin/job-board.stats.candidates.description')),

            Stat::make(
                __('widgets/admin/job-board.stats.organizations.label'),
                sprintf('%d (%d)', (int) ($orgStats->total ?? 0), (int) ($orgStats->verified ?? 0)),
            )->description(__('widgets/admin/job-board.stats.organizations.description')),

            Stat::make(
                __('widgets/admin/job-board.stats.offers.label'),
                (string) $activeOffers,
            )->description(__('widgets/admin/job-board.stats.offers.description')),

            Stat::make(
                __('widgets/admin/job-board.stats.applications.label'),
                (string) $recentApplications,
            )->description(__('widgets/admin/job-board.stats.applications.description')),
        ];
    }
}
