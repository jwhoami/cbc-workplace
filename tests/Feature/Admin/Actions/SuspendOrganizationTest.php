<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Actions;

use App\Actions\Admin\SuspendOrganization;
use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Mail\Organization\Suspended;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SuspendOrganizationTest extends TestCase
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

    public function test_t1_suspends_verified_org_and_cascades_active_offers_only(): void
    {
        Mail::fake();

        $member = Member::factory()->create(['is_active' => true]);
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        JobListing::factory()->count(3)->active()->create([
            'organization_id' => $org->id,
            'member_id' => $member->id,
        ]);
        $closedOffer = JobListing::factory()->closed()->create([
            'organization_id' => $org->id,
            'member_id' => $member->id,
        ]);

        $result = SuspendOrganization::run($org, 'Razón de prueba');

        $org->refresh();
        $this->assertTrue($result->wasSuspended());
        $this->assertSame(3, $result->offersDeactivated);
        $this->assertTrue($org->is_suspended());
        $this->assertSame(OrganizationVerificationState::VERIFIED, $org->verification_state);
        $this->assertSame(
            3,
            JobListing::query()->where('organization_id', $org->id)->where('state', JobListingState::CLOSED)
                ->where('id', '!=', $closedOffer->id)->count()
        );
        $closedOffer->refresh();
        $this->assertSame(JobListingState::CLOSED, $closedOffer->state);
    }

    public function test_t2_pending_org_can_be_suspended_with_verification_state_preserved(): void
    {
        Mail::fake();

        $member = Member::factory()->create();
        $org = Organization::factory()->pending()->create(['member_id' => $member->id]);

        SuspendOrganization::run($org);

        $org->refresh();
        $this->assertTrue($org->is_suspended());
        $this->assertSame(OrganizationVerificationState::PENDING, $org->verification_state);
    }

    public function test_t3_reason_is_trimmed_and_not_present_in_mail_body(): void
    {
        Mail::fake();

        $member = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        SuspendOrganization::run($org, '  Sensitive internal note 9876  ');

        $org->refresh();
        $this->assertSame('Sensitive internal note 9876', $org->suspension_reason);

        Mail::assertQueued(Suspended::class, function (Suspended $mail) {
            $rendered = $mail->render();

            return ! str_contains($rendered, 'Sensitive internal note 9876');
        });
    }

    public function test_t4_blank_or_whitespace_reason_normalizes_to_null(): void
    {
        Mail::fake();

        $member = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        SuspendOrganization::run($org, '   ');

        $org->refresh();
        $this->assertNull($org->suspension_reason);
    }

    public function test_t5_already_suspended_short_circuits_with_no_writes(): void
    {
        Mail::fake();
        Queue::fake();

        $member = Member::factory()->create();
        $org = Organization::factory()->verifiedSuspended()->create(['member_id' => $member->id]);

        $logsBefore = DB::table('activity_log')->count();
        $commentsBefore = DB::table('comments')->count();

        $result = SuspendOrganization::run($org, 'no-op');

        $this->assertTrue($result->wasAlreadySuspended());
        $this->assertSame($logsBefore, DB::table('activity_log')->count());
        $this->assertSame($commentsBefore, DB::table('comments')->count());
        Mail::assertNothingQueued();
    }

    public function test_t7_mail_failure_does_not_revert_suspension(): void
    {
        $member = Member::factory()->create(['email' => 'valid-but-fail@example.com']);
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        Mail::shouldReceive('to')->andReturnUsing(function () {
            throw new \RuntimeException('simulated mailer outage');
        });

        $result = SuspendOrganization::run($org);

        $org->refresh();
        $this->assertTrue($result->wasSuspended());
        $this->assertTrue($org->is_suspended());
        $this->assertSame(0, $result->notificationsEnqueued);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Organization::class,
            'subject_id' => $org->id,
            'event' => 'mail-suspension-dispatch-failed',
        ]);
    }

    public function test_activity_log_records_suspension_with_reason_in_properties(): void
    {
        Mail::fake();

        $member = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        SuspendOrganization::run($org, 'Motivo registrado');

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Organization::class,
            'subject_id' => $org->id,
            'event' => 'organization-suspended',
        ]);

        $row = DB::table('activity_log')
            ->where('subject_id', $org->id)
            ->where('event', 'organization-suspended')
            ->latest('id')
            ->first();
        $this->assertNotNull($row);
        $props = json_decode($row->properties, true);
        $this->assertSame('Motivo registrado', $props['suspension_reason']);
        $this->assertSame(0, (int) $props['offers_deactivated']);
    }

    public function test_one_mail_enqueued_per_admin_member(): void
    {
        Mail::fake();

        $member = Member::factory()->create(['email' => 'admin1@example.com']);
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        SuspendOrganization::run($org);

        Mail::assertQueued(Suspended::class, 1);
        Mail::assertQueued(Suspended::class, fn (Suspended $mail) => $mail->hasTo('admin1@example.com'));
    }
}
