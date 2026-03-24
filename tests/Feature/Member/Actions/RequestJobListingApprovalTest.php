<?php

namespace Tests\Feature\Member\Actions;

use App\Actions\Member\RequestJobListingApproval;
use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Mail\Admin\JobListingSubmitted;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class RequestJobListingApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $memberRole = Role::create([
            'name' => 'member',
            'title' => 'Member',
            'is_active' => true,
            'is_admin' => false,
            'perm' => [],
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

        Livewire::actingAs($this->member, 'member');
        $this->get('/member');
    }

    public function test_can_submit_draft_listing_for_approval(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->draft()->create();

        RequestJobListingApproval::run($listing);

        $listing->refresh();
        $this->assertEquals(JobListingState::PENDING, $listing->state);
    }

    public function test_can_resubmit_rejected_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->rejected()->create();

        RequestJobListingApproval::run($listing);

        $listing->refresh();
        $this->assertEquals(JobListingState::PENDING, $listing->state);
    }

    public function test_fails_on_unverified_organization(): void
    {
        $this->organization->update(['verification_state' => OrganizationVerificationState::PENDING]);

        $listing = JobListing::factory()->forOrganization($this->organization)->draft()->create();

        $this->expectException(\Exception::class);
        RequestJobListingApproval::run($listing);
    }

    public function test_fails_on_past_deadline(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->draft()->create([
            'application_deadline' => now()->subDay(),
        ]);

        $this->expectException(\Exception::class);
        RequestJobListingApproval::run($listing);
    }

    public function test_fails_on_active_listing(): void
    {
        $listing = JobListing::factory()->forOrganization($this->organization)->active()->create();

        $this->expectException(\Exception::class);
        RequestJobListingApproval::run($listing);
    }

    public function test_sends_email_to_admin_approvers(): void
    {
        Mail::fake();

        $adminRole = Role::create([
            'name' => 'admin',
            'title' => 'Admin',
            'is_active' => true,
            'is_admin' => true,
            'perm' => [],
        ]);

        $admin = User::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'can_sponsor' => true,
            'role_id' => $adminRole->id,
        ]);

        $listing = JobListing::factory()->forOrganization($this->organization)->draft()->create();

        RequestJobListingApproval::run($listing);

        Mail::assertSent(JobListingSubmitted::class, fn ($mail) => $mail->hasTo($admin->email));
    }
}
