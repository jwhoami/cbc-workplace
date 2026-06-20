<?php

namespace Tests\Feature\Member\Actions;

use App\Actions\Member\RequestOrganizationVerification;
use App\Enums\OrganizationVerificationState;
use App\Mail\Organization\VerificationRequested;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RequestOrganizationVerificationTest extends TestCase
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
            'role_id' => $memberRole->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $this->member->id,
        ]);

        // Create an admin role with an active user (approver)
        $adminRole = Role::create([
            'name' => 'admin',
            'title' => 'Administrator',
            'is_active' => true,
            'is_admin' => true,
            'perm' => ['*.*'],
        ]);

        User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->member, 'member');
    }

    public function test_member_can_request_verification_for_pending_org(): void
    {
        Mail::fake();

        RequestOrganizationVerification::run($this->organization);

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Organization::class,
            'commentable_id' => $this->organization->id,
        ]);
    }

    public function test_requesting_verification_for_verified_org_throws_exception(): void
    {
        $this->expectException(\Exception::class);

        $this->organization->forceFill([
            'verification_state' => OrganizationVerificationState::VERIFIED,
        ])->save();

        RequestOrganizationVerification::run($this->organization);
    }

    public function test_comment_is_added_on_request(): void
    {
        Mail::fake();

        RequestOrganizationVerification::run($this->organization);

        $comment = $this->organization->comments()->latest()->first();
        $this->assertNotNull($comment);
        $this->assertStringContainsString('Verificación solicitada por', $comment->comment);
    }

    public function test_mail_is_sent_to_approvers(): void
    {
        Mail::fake();

        RequestOrganizationVerification::run($this->organization);

        Mail::assertSent(VerificationRequested::class);
    }
}
