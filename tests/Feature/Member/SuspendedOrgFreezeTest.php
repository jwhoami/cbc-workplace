<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SuspendedOrgFreezeTest extends TestCase
{
    use RefreshDatabase;

    private Member $memberControl;

    private Organization $orgControl;

    private Member $memberFrozen;

    private Organization $orgFrozen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memberControl = Member::factory()->create(['is_active' => true]);
        $this->orgControl = Organization::factory()->verified()->create(['member_id' => $this->memberControl->id]);

        $this->memberFrozen = Member::factory()->create(['is_active' => true]);
        $this->orgFrozen = Organization::factory()->verifiedSuspended('private')->create([
            'member_id' => $this->memberFrozen->id,
        ]);
    }

    public function test_member_with_suspended_org_cannot_create_new_job_listing(): void
    {
        $this->actingAs($this->memberFrozen, 'member');
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('member'));

        $this->assertFalse(Gate::forUser($this->memberFrozen)->allows('create', JobListing::class));
    }

    public function test_member_with_active_org_can_still_create_job_listing(): void
    {
        $this->actingAs($this->memberControl, 'member');
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('member'));

        $this->assertTrue(Gate::forUser($this->memberControl)->allows('create', JobListing::class));
    }

    public function test_member_with_suspended_org_cannot_update_existing_job_listing(): void
    {
        $listing = JobListing::factory()->draft()->create([
            'organization_id' => $this->orgFrozen->id,
            'member_id' => $this->memberFrozen->id,
        ]);

        $this->actingAs($this->memberFrozen, 'member');
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('member'));

        $this->assertFalse(Gate::forUser($this->memberFrozen)->allows('update', $listing));
        $this->assertFalse(Gate::forUser($this->memberFrozen)->allows('delete', $listing));
        $this->assertFalse(Gate::forUser($this->memberFrozen)->allows('close', $listing));
    }

    public function test_member_with_suspended_org_cannot_change_application_status(): void
    {
        $listing = JobListing::factory()->active()->create([
            'organization_id' => $this->orgFrozen->id,
            'member_id' => $this->memberFrozen->id,
        ]);
        $application = Application::factory()->create([
            'job_listing_id' => $listing->id,
            'member_id' => Member::factory()->create()->id,
        ]);

        $this->actingAs($this->memberFrozen, 'member');
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('member'));

        $this->assertFalse(Gate::forUser($this->memberFrozen)->allows('update', $application));
    }

    public function test_member_with_suspended_org_cannot_edit_organization_profile(): void
    {
        $this->actingAs($this->memberFrozen, 'member');
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('member'));

        $this->assertFalse(Gate::forUser($this->memberFrozen)->allows('update', $this->orgFrozen));
    }

    public function test_read_access_is_preserved_for_suspended_org_member(): void
    {
        $listing = JobListing::factory()->active()->create([
            'organization_id' => $this->orgFrozen->id,
            'member_id' => $this->memberFrozen->id,
        ]);

        $this->actingAs($this->memberFrozen, 'member');
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('member'));

        $this->assertTrue(Gate::forUser($this->memberFrozen)->allows('view', $listing));
        $this->assertTrue(Gate::forUser($this->memberFrozen)->allows('viewAny', JobListing::class));
    }
}
