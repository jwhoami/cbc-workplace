<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\OrganizationVerificationState;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationIsSuspendedTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_suspended_returns_true_when_suspended_at_is_set(): void
    {
        $org = Organization::factory()->verifiedSuspended()->for(Member::factory(), 'member')->create();

        $this->assertTrue($org->is_suspended());
    }

    public function test_is_suspended_returns_false_when_suspended_at_is_null(): void
    {
        $org = Organization::factory()->verified()->for(Member::factory(), 'member')->create();

        $this->assertFalse($org->is_suspended());
    }

    public function test_can_be_suspended_when_not_suspended(): void
    {
        $org = Organization::factory()->verified()->for(Member::factory(), 'member')->create();

        $this->assertTrue($org->canBeSuspended());
        $this->assertFalse($org->canBeReactivated());
    }

    public function test_can_be_reactivated_when_currently_suspended(): void
    {
        $org = Organization::factory()->verifiedSuspended()->for(Member::factory(), 'member')->create();

        $this->assertFalse($org->canBeSuspended());
        $this->assertTrue($org->canBeReactivated());
    }

    public function test_profile_should_hide_public_data_mirrors_is_suspended(): void
    {
        $suspended = Organization::factory()->pendingSuspended()->for(Member::factory(), 'member')->create();
        $live = Organization::factory()->verified()->for(Member::factory(), 'member')->create();

        $this->assertTrue($suspended->profileShouldHidePublicData());
        $this->assertFalse($live->profileShouldHidePublicData());
    }

    public function test_excluding_suspended_scope_filters_out_suspended_rows(): void
    {
        Organization::factory()->verified()->for(Member::factory(), 'member')->create();
        Organization::factory()->pending()->for(Member::factory(), 'member')->create();
        Organization::factory()->verifiedSuspended()->for(Member::factory(), 'member')->create();

        $this->assertSame(3, Organization::query()->count());
        $this->assertSame(2, Organization::query()->excludingSuspended()->count());
    }

    public function test_suspension_is_orthogonal_to_verification_state(): void
    {
        $org = Organization::factory()->verifiedSuspended()->for(Member::factory(), 'member')->create();

        $this->assertTrue($org->is_suspended());
        $this->assertSame(OrganizationVerificationState::VERIFIED, $org->verification_state);
    }
}
