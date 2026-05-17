<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Spec 009 demo seeder — populates a realistic universe so the manual
 * verification walk-through in quickstart.md exercises every widget,
 * the navigation group, and the suspension flow.
 *
 * Idempotent: callers should `migrate:fresh` before invoking. Will
 * upsert the named admin and moderator users so a re-run does not
 * duplicate them.
 */
class Spec009DemoSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrCreate (not firstOr): RoleSeeder runs first in DatabaseSeeder and
        // creates DEACONO with perm => []. Without overwriting, the moderator user
        // ends up powerless and §2 RBAC quickstart steps cannot exercise FR-021.
        $adminRole = Role::query()->updateOrCreate(
            ['name' => 'ADMIN'],
            ['title' => 'ADMIN', 'is_active' => true, 'is_admin' => true, 'perm' => []],
        );
        $moderatorRole = Role::query()->updateOrCreate(
            ['name' => 'DEACONO'],
            ['title' => 'DEACONO', 'is_active' => true, 'is_admin' => false, 'perm' => ['Admin.Organization.viewAny']],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role_id' => $adminRole->id,
                'username' => 'admin_spec009',
                'name' => 'Admin Spec009',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_blocked' => false,
            ],
        );
        User::query()->updateOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'role_id' => $moderatorRole->id,
                'username' => 'moderator_spec009',
                'name' => 'Moderator Spec009',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_blocked' => false,
            ],
        );

        $candidates = Member::factory()->count(5)->create(['is_active' => true]);
        foreach ($candidates as $c) {
            CandidateProfile::factory()->create(['member_id' => $c->id]);
        }

        $verifiedMembers = Member::factory()->count(2)->create(['is_active' => true]);
        $verifiedOrgs = collect();
        foreach ($verifiedMembers as $m) {
            $verifiedOrgs->push(Organization::factory()->verified()->create(['member_id' => $m->id]));
        }

        $suspendTargetMember = Member::factory()->create([
            'email' => 'org-admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $suspendTarget = Organization::factory()->verified()->create([
            'member_id' => $suspendTargetMember->id,
            'display_name' => 'Iglesia Demostración Suspender',
        ]);
        $verifiedOrgs->push($suspendTarget);

        $pendingMembers = Member::factory()->count(7)->create(['is_active' => true]);
        foreach ($pendingMembers as $m) {
            Organization::factory()->create([
                'member_id' => $m->id,
                'verification_state' => OrganizationVerificationState::PENDING,
            ]);
        }

        // 2 already-suspended orgs simulate legacy-backfill survivors.
        // Use pendingSuspended() so the dashboard "verificadas" count stays at 3
        // (the legacy rows mirror the post-migration backfill state per R12).
        $legacyMembers = Member::factory()->count(2)->create(['is_active' => true]);
        foreach ($legacyMembers as $m) {
            Organization::factory()->pendingSuspended('Suspensión histórica', 'Legacy Admin')->create([
                'member_id' => $m->id,
            ]);
        }

        JobListing::factory()->count(3)->active()->create([
            'organization_id' => $suspendTarget->id,
            'member_id' => $suspendTarget->member_id,
        ]);

        $verifiedOrgsForActive = $verifiedOrgs->reject(fn (Organization $o) => $o->is($suspendTarget))->values();
        for ($i = 0; $i < 22; $i++) {
            $org = $verifiedOrgsForActive->random();
            JobListing::factory()->active()->create([
                'organization_id' => $org->id,
                'member_id' => $org->member_id,
            ]);
        }

        for ($i = 0; $i < 6; $i++) {
            $org = $verifiedOrgs->random();
            JobListing::factory()->pending()->create([
                'organization_id' => $org->id,
                'member_id' => $org->member_id,
            ]);
        }

        for ($i = 0; $i < 4; $i++) {
            $org = $verifiedOrgs->random();
            JobListing::factory()->closed()->create([
                'organization_id' => $org->id,
                'member_id' => $org->member_id,
            ]);
        }

        $allActiveListings = JobListing::query()->where('state', JobListingState::ACTIVE)->get();

        // The applications table enforces UNIQUE (job_listing_id, member_id).
        // Build a deterministic pool of (listing, candidate) pairs to avoid
        // duplicate violations when the seed asks for more rows than candidates.
        $pairs = collect();
        foreach ($allActiveListings as $listing) {
            foreach ($candidates as $candidate) {
                $pairs->push(['listing' => $listing, 'candidate' => $candidate]);
            }
        }
        $pairs = $pairs->shuffle();

        $recentTarget = 7;
        $olderTarget = 11;

        foreach ($pairs->take($recentTarget) as $pair) {
            Application::factory()->create([
                'job_listing_id' => $pair['listing']->id,
                'member_id' => $pair['candidate']->id,
                'submitted_at' => now()->subHours(random_int(1, 23)),
            ]);
        }

        foreach ($pairs->slice($recentTarget, $olderTarget) as $pair) {
            Application::factory()->create([
                'job_listing_id' => $pair['listing']->id,
                'member_id' => $pair['candidate']->id,
                'submitted_at' => now()->subDays(random_int(2, 30)),
            ]);
        }
    }
}
