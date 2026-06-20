<?php

namespace Tests\Feature\Admin\Resources;

use App\Filament\Admin\Resources\CandidateProfileResource;
use App\Models\CandidateProfile;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateProfileResourceTest extends TestCase
{
    use RefreshDatabase;

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

        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($user, 'admin');
        $this->get('/admin');
    }

    public function test_admin_can_render_list_page(): void
    {
        $member = Member::factory()->create(['is_active' => true]);
        CandidateProfile::factory()->create(['member_id' => $member->id]);

        $this->get(CandidateProfileResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_admin_can_view_profile_detail(): void
    {
        $member = Member::factory()->create(['is_active' => true]);
        $profile = CandidateProfile::factory()->create(['member_id' => $member->id]);

        $this->get(CandidateProfileResource::getUrl('view', ['record' => $profile]))
            ->assertSuccessful();
    }

    public function test_admin_list_shows_correct_columns(): void
    {
        $member = Member::factory()->create(['is_active' => true, 'name' => 'Test Member']);
        CandidateProfile::factory()->create([
            'member_id' => $member->id,
            'headline' => 'Senior Developer',
            'city' => 'Ciudad de Panamá',
            'is_visible' => true,
        ]);

        Livewire::test(CandidateProfileResource\Pages\ListCandidateProfiles::class)
            ->assertCanSeeTableRecords(CandidateProfile::all());
    }
}
