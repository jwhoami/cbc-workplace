<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Actions\Admin\SuspendOrganization;
use App\Actions\Public\SearchPublicOffersAction;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SuspendedOrgPublicContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_should_hide_public_data_returns_true_for_suspended_org(): void
    {
        $member = Member::factory()->create();
        $suspended = Organization::factory()->verifiedSuspended()->create(['member_id' => $member->id]);
        $live = Organization::factory()->verified()->create(['member_id' => Member::factory()->create()->id]);

        $this->assertTrue($suspended->profileShouldHidePublicData());
        $this->assertFalse($live->profileShouldHidePublicData());
    }

    public function test_suspension_cascade_removes_offers_from_public_search(): void
    {
        Mail::fake();

        $adminRole = Role::create([
            'name' => 'admin', 'title' => 'Administrator',
            'is_active' => true, 'is_admin' => true, 'perm' => ['*.*'],
        ]);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
        $this->actingAs($admin, 'admin');

        $member = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        JobListing::factory()->count(3)->active()->create([
            'organization_id' => $org->id,
            'member_id' => $member->id,
        ]);

        $search = new SearchPublicOffersAction;
        $before = $search->handle()->total();
        $this->assertSame(3, $before);

        SuspendOrganization::run($org);

        $after = $search->handle()->total();
        $this->assertSame(0, $after);
    }
}
