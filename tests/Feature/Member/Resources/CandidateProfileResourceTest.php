<?php

namespace Tests\Feature\Member\Resources;

use App\Filament\Member\Resources\CandidateProfileResource;
use App\Filament\Member\Resources\CandidateProfileResource\RelationManagers\EducationsRelationManager;
use App\Filament\Member\Resources\CandidateProfileResource\RelationManagers\WorkExperiencesRelationManager;
use App\Models\CandidateProfile;
use App\Models\Education;
use App\Models\Member;
use App\Models\Role;
use App\Models\WorkExperience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateProfileResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

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

        Livewire::actingAs($this->member, 'member');
        $this->get('/member');
    }

    public function test_member_can_render_create_page(): void
    {
        Livewire::test(CandidateProfileResource\Pages\CreateCandidateProfile::class)
            ->assertSuccessful();
    }

    public function test_member_can_create_profile_with_required_fields(): void
    {
        Livewire::test(CandidateProfileResource\Pages\CreateCandidateProfile::class)
            ->fillForm([
                'headline' => 'Desarrollador Full Stack',
                'summary' => 'Profesional con 5 años de experiencia',
                'city' => 'Ciudad de Panamá',
                'province' => 'Panamá',
                'phone' => '+507 6000-0000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('candidate_profiles', [
            'member_id' => $this->member->id,
            'headline' => 'Desarrollador Full Stack',
        ]);
    }

    public function test_member_with_profile_is_redirected_to_edit(): void
    {
        CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        Livewire::test(CandidateProfileResource\Pages\CreateCandidateProfile::class)
            ->assertRedirect();
    }

    public function test_member_can_edit_profile_fields(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        Livewire::test(CandidateProfileResource\Pages\EditCandidateProfile::class, ['record' => $profile->id])
            ->fillForm([
                'headline' => 'Updated Headline',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('candidate_profiles', [
            'id' => $profile->id,
            'headline' => 'Updated Headline',
        ]);
    }

    public function test_profile_starts_with_is_visible_true(): void
    {
        Livewire::test(CandidateProfileResource\Pages\CreateCandidateProfile::class)
            ->fillForm([
                'headline' => 'Test Profile',
                'summary' => 'Test summary',
                'city' => 'Colón',
                'province' => 'Colón',
                'phone' => '+507 6111-1111',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $profile = CandidateProfile::where('member_id', $this->member->id)->first();
        $this->assertNotNull($profile);
        $this->assertTrue($profile->is_visible);
    }

    public function test_member_can_toggle_visibility_to_hidden(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
            'is_visible' => true,
        ]);

        Livewire::test(CandidateProfileResource\Pages\EditCandidateProfile::class, ['record' => $profile->id])
            ->fillForm([
                'is_visible' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('candidate_profiles', [
            'id' => $profile->id,
            'is_visible' => false,
        ]);
    }

    // === Work Experience Tests (US2) ===

    public function test_can_create_work_experience_via_relation_manager(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        Livewire::test(WorkExperiencesRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('create', data: [
                'company' => 'Tech Corp Panamá',
                'position' => 'Senior Developer',
                'description' => 'Desarrollo de aplicaciones web',
                'start_date' => '2022-01-15',
                'is_current' => true,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('work_experiences', [
            'candidate_profile_id' => $profile->id,
            'company' => 'Tech Corp Panamá',
            'is_current' => true,
            'end_date' => null,
        ]);
    }

    public function test_work_experiences_sorted_by_start_date_desc(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        $older = WorkExperience::factory()->create([
            'candidate_profile_id' => $profile->id,
            'start_date' => '2020-01-01',
            'end_date' => '2021-12-31',
        ]);

        $newer = WorkExperience::factory()->create([
            'candidate_profile_id' => $profile->id,
            'start_date' => '2022-01-01',
            'end_date' => '2023-12-31',
        ]);

        Livewire::test(WorkExperiencesRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->assertCanSeeTableRecords([$newer, $older], inOrder: true);
    }

    public function test_can_edit_work_experience(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        $experience = WorkExperience::factory()->create([
            'candidate_profile_id' => $profile->id,
        ]);

        Livewire::test(WorkExperiencesRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('edit', $experience, data: [
                'company' => 'Updated Company',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('work_experiences', [
            'id' => $experience->id,
            'company' => 'Updated Company',
        ]);
    }

    public function test_can_delete_work_experience(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        $experience = WorkExperience::factory()->create([
            'candidate_profile_id' => $profile->id,
        ]);

        Livewire::test(WorkExperiencesRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('delete', $experience);

        $this->assertDatabaseMissing('work_experiences', [
            'id' => $experience->id,
        ]);
    }

    public function test_current_job_has_no_end_date(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        Livewire::test(WorkExperiencesRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('create', data: [
                'company' => 'Current Job Corp',
                'position' => 'Developer',
                'description' => 'Working here now',
                'start_date' => '2023-06-01',
                'is_current' => true,
            ])
            ->assertHasNoTableActionErrors();

        $experience = WorkExperience::where('company', 'Current Job Corp')->first();
        $this->assertNotNull($experience);
        $this->assertTrue($experience->is_current);
        $this->assertNull($experience->end_date);
    }

    // === Education Tests (US3) ===

    public function test_can_create_education_via_relation_manager(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        Livewire::test(EducationsRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('create', data: [
                'institution' => 'Universidad de Panamá',
                'degree' => 'Licenciatura en Informática',
                'field_of_study' => 'Ciencias de la Computación',
                'graduation_year' => 2020,
                'is_in_progress' => false,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('educations', [
            'candidate_profile_id' => $profile->id,
            'institution' => 'Universidad de Panamá',
            'graduation_year' => 2020,
        ]);
    }

    public function test_can_edit_education(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        $education = Education::factory()->create([
            'candidate_profile_id' => $profile->id,
        ]);

        Livewire::test(EducationsRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('edit', $education, data: [
                'institution' => 'Updated University',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('educations', [
            'id' => $education->id,
            'institution' => 'Updated University',
        ]);
    }

    public function test_can_delete_education(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        $education = Education::factory()->create([
            'candidate_profile_id' => $profile->id,
        ]);

        Livewire::test(EducationsRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('delete', $education);

        $this->assertDatabaseMissing('educations', [
            'id' => $education->id,
        ]);
    }

    public function test_in_progress_education_has_no_graduation_year(): void
    {
        $profile = CandidateProfile::factory()->create([
            'member_id' => $this->member->id,
        ]);

        Livewire::test(EducationsRelationManager::class, [
            'ownerRecord' => $profile,
            'pageClass' => CandidateProfileResource\Pages\EditCandidateProfile::class,
        ])
            ->callTableAction('create', data: [
                'institution' => 'Universidad Tecnológica',
                'degree' => 'Maestría en Datos',
                'field_of_study' => 'Ciencia de Datos',
                'is_in_progress' => true,
            ])
            ->assertHasNoTableActionErrors();

        $education = Education::where('institution', 'Universidad Tecnológica')->first();
        $this->assertNotNull($education);
        $this->assertTrue($education->is_in_progress);
        $this->assertNull($education->graduation_year);
    }
}
