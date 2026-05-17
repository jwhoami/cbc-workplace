<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Actions;

use App\Actions\Admin\OrganizationVerification;
use App\Enums\OrganizationVerificationState;
use App\Mail\Organization\Verified;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganizationVerificationTest extends TestCase
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
            'title' => 'Administrator',
            'is_active' => true,
            'is_admin' => true,
            'perm' => ['*.*'],
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->member = Member::factory()->create([
            'is_active' => true,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $this->member->id,
        ]);

        $this->actingAs($this->admin, 'admin');
    }

    public function test_admin_can_verify_pending_organization(): void
    {
        Mail::fake();

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::VERIFIED->value,
        ]);

        $this->organization->refresh();
        $this->assertEquals(OrganizationVerificationState::VERIFIED, $this->organization->verification_state);
        $this->assertTrue($this->organization->is_active);
    }

    public function test_verification_does_not_touch_suspension_flag(): void
    {
        Mail::fake();

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::VERIFIED->value,
        ]);

        $this->organization->refresh();
        $this->assertNull($this->organization->suspended_at);
        $this->assertNull($this->organization->suspended_by);
        $this->assertNull($this->organization->suspension_reason);
    }

    public function test_verification_sets_verification_by_and_verified_at(): void
    {
        Mail::fake();

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::VERIFIED->value,
        ]);

        $this->organization->refresh();
        $this->assertEquals($this->admin->name, $this->organization->verification_by);
        $this->assertNotNull($this->organization->verified_at);
    }

    public function test_activity_log_records_verification(): void
    {
        Mail::fake();

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::VERIFIED->value,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Organization::class,
            'subject_id' => $this->organization->id,
            'event' => 'organization-verified',
        ]);
    }

    public function test_comment_is_added_on_verify(): void
    {
        Mail::fake();

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::VERIFIED->value,
        ]);

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Organization::class,
            'commentable_id' => $this->organization->id,
            'comment' => 'Organización verificada',
        ]);
    }

    public function test_mail_is_sent_to_member_on_verify(): void
    {
        Mail::fake();

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::VERIFIED->value,
        ]);

        Mail::assertSent(Verified::class, function (Verified $mail) {
            return $mail->hasTo($this->member->email);
        });
    }

    public function test_verification_action_rejects_suspended_decision(): void
    {
        Mail::fake();
        $this->expectException(\Exception::class);

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::SUSPENDED->value,
            'verification_reason' => 'Motivo de suspensión',
        ]);
    }

    public function test_invalid_decision_throws_exception(): void
    {
        $this->expectException(\Exception::class);

        OrganizationVerification::run($this->organization, [
            'decision' => OrganizationVerificationState::PENDING->value,
        ]);
    }
}
