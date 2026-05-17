<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Actions;

use App\Actions\Admin\ReactivateOrganization;
use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReactivateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin', 'title' => 'Administrator',
            'is_active' => true, 'is_admin' => true, 'perm' => ['*.*'],
        ]);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
        $this->actingAs($this->admin, 'admin');
    }

    /**
     * @dataProvider verificationStateProvider
     */
    public function test_t1_t2_reactivation_preserves_verification_state(OrganizationVerificationState $state): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->suspended()->create([
            'member_id' => $member->id,
            'verification_state' => $state,
            'verification_by' => $state === OrganizationVerificationState::VERIFIED ? 'Admin Test' : null,
            'verified_at' => $state === OrganizationVerificationState::VERIFIED ? now() : null,
        ]);

        $result = ReactivateOrganization::run($org);

        $org->refresh();
        $this->assertTrue($result->wasReactivated());
        $this->assertNull($org->suspended_at);
        $this->assertNull($org->suspended_by);
        $this->assertNull($org->suspension_reason);
        $this->assertSame($state, $org->verification_state);
    }

    public static function verificationStateProvider(): array
    {
        return [
            'pending' => [OrganizationVerificationState::PENDING],
            'verified' => [OrganizationVerificationState::VERIFIED],
        ];
    }

    public function test_t3_reactivation_does_not_reactivate_closed_offers(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verifiedSuspended()->create(['member_id' => $member->id]);

        JobListing::factory()->count(3)->closed()->create([
            'organization_id' => $org->id,
            'member_id' => $member->id,
        ]);

        ReactivateOrganization::run($org);

        $this->assertSame(
            3,
            JobListing::query()->where('organization_id', $org->id)->where('state', JobListingState::CLOSED)->count()
        );
        $this->assertSame(
            0,
            JobListing::query()->where('organization_id', $org->id)->where('state', JobListingState::ACTIVE)->count()
        );
    }

    public function test_t4_returns_not_suspended_for_already_active_org(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        $logsBefore = DB::table('activity_log')->count();
        $result = ReactivateOrganization::run($org);

        $this->assertTrue($result->wasNotSuspended());
        $this->assertSame($logsBefore, DB::table('activity_log')->count());
    }

    public function test_t5_prior_suspension_log_retains_its_reason_after_reactivation(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verifiedSuspended('Razón crítica', 'Admin Test')->create([
            'member_id' => $member->id,
        ]);

        // Simulate the historic suspension log that the SuspendOrganization
        // action would have written when the org was first suspended.
        activity('default')
            ->event('organization-suspended')
            ->performedOn($org)
            ->withProperties(['suspension_reason' => 'Razón crítica'])
            ->log('Organización suspendida');

        ReactivateOrganization::run($org);

        $row = DB::table('activity_log')
            ->where('subject_id', $org->id)
            ->where('event', 'organization-suspended')
            ->first();
        $props = json_decode($row->properties, true);
        $this->assertSame('Razón crítica', $props['suspension_reason']);

        $org->refresh();
        $this->assertNull($org->suspension_reason);
    }

    public function test_reactivation_records_activity_log_entry(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verifiedSuspended()->create(['member_id' => $member->id]);

        ReactivateOrganization::run($org);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Organization::class,
            'subject_id' => $org->id,
            'event' => 'organization-reactivated',
        ]);
    }
}
