<?php

namespace Tests\Feature\Admin\Actions;

use App\Actions\Admin\JobListingApproval;
use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Mail\Member\JobListingApproved;
use App\Mail\Member\JobListingRejected;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JobListingApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Member $member;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin',
            'title' => 'Admin',
            'is_active' => true,
            'is_admin' => true,
            'perm' => [],
        ]);

        $memberRole = Role::create([
            'name' => 'member',
            'title' => 'Member',
            'is_active' => true,
            'is_admin' => false,
            'perm' => [],
        ]);

        $this->admin = User::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $adminRole->id,
        ]);

        $this->member = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $memberRole->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $this->member->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);

        $this->actingAs($this->admin);
    }

    public function test_can_approve_pending_listing(): void
    {
        Mail::fake();

        $listing = JobListing::factory()->forOrganization($this->organization)->pending()->create();

        JobListingApproval::run($listing, [
            'decision' => JobListingState::ACTIVE->value,
            'approval_reason' => 'Se ve bien',
        ]);

        $listing->refresh();
        $this->assertEquals(JobListingState::ACTIVE, $listing->state);
        $this->assertNotNull($listing->published_at);
        $this->assertEquals($this->admin->name, $listing->approval_by);

        Mail::assertSent(JobListingApproved::class, fn ($mail) => $mail->hasTo($this->member->email));
    }

    public function test_can_reject_pending_listing(): void
    {
        Mail::fake();

        $listing = JobListing::factory()->forOrganization($this->organization)->pending()->create();

        JobListingApproval::run($listing, [
            'decision' => JobListingState::REJECTED->value,
            'approval_reason' => 'Falta información sobre requisitos.',
        ]);

        $listing->refresh();
        $this->assertEquals(JobListingState::REJECTED, $listing->state);
        $this->assertEquals('Falta información sobre requisitos.', $listing->approval_reason);

        Mail::assertSent(JobListingRejected::class, fn ($mail) => $mail->hasTo($this->member->email));
    }

    public function test_fails_on_non_pending_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->draft()->create();

        $this->expectException(\Exception::class);
        JobListingApproval::run($listing, [
            'decision' => JobListingState::ACTIVE->value,
            'approval_reason' => 'Ok',
        ]);
    }
}
