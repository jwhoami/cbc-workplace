<?php

namespace Tests\Feature\Security;

use App\Enums\ApplicationStatus;
use App\Enums\JobListingState;
use App\Enums\OrganizationVerificationState;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\CandidateProfile;
use App\Models\Education;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\WorkExperience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for issue #9 — verify that protected (state, FK,
 * snapshot, and admin-only) columns cannot be mass-assigned via
 * Model::create() / ->update() on Bolsa de Trabajo models.
 */
class MassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_protects_state_and_foreign_keys_from_mass_assignment(): void
    {
        $application = (new Application())->fill([
            'cover_letter' => 'Original letter',
            'screening_answers' => [['question' => 'Q', 'answer' => 'A']],
            'status' => ApplicationStatus::ACCEPTED,
            'member_id' => 99,
            'job_listing_id' => 99,
            'candidate_profile_id' => 99,
            'anonymized_at' => now(),
            'last_status_changed_by' => 'attacker',
            'candidate_name_snapshot' => 'Spoofed Name',
        ]);

        $this->assertNull($application->status, 'status must not be mass-assignable');
        $this->assertNull($application->member_id, 'member_id must not be mass-assignable');
        $this->assertNull($application->job_listing_id, 'job_listing_id must not be mass-assignable');
        $this->assertNull($application->candidate_profile_id, 'candidate_profile_id must not be mass-assignable');
        $this->assertNull($application->anonymized_at, 'anonymized_at must not be mass-assignable');
        $this->assertNull($application->last_status_changed_by, 'last_status_changed_by must not be mass-assignable');
        $this->assertNull($application->candidate_name_snapshot, 'snapshot fields must not be mass-assignable');
        $this->assertEquals('Original letter', $application->cover_letter, 'user-facing fields remain fillable');
    }

    public function test_organization_protects_verification_state_from_mass_assignment(): void
    {
        $member = $this->createMember();
        $organization = Organization::factory()->create([
            'member_id' => $member->id,
            'verification_state' => OrganizationVerificationState::PENDING,
        ]);

        $organization->update([
            'display_name' => 'Updated Name',
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verification_by' => 'attacker',
            'verified_at' => now(),
            'is_active' => false,
        ]);

        $organization->refresh();
        $this->assertEquals('Updated Name', $organization->display_name);
        $this->assertEquals(OrganizationVerificationState::PENDING, $organization->verification_state);
        $this->assertNull($organization->verification_by);
        $this->assertNull($organization->verified_at);
        $this->assertTrue($organization->is_active);
    }

    public function test_job_listing_protects_state_and_approval_fields_from_mass_assignment(): void
    {
        $member = $this->createMember();
        $organization = Organization::factory()->create([
            'member_id' => $member->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
        ]);
        $listing = JobListing::factory()->forOrganization($organization)->draft()->create();

        $listing->update([
            'title' => 'Updated Title',
            'state' => JobListingState::ACTIVE,
            'approval_by' => 'attacker',
            'approval_at' => now(),
            'view_count' => 99999,
            'organization_id' => 99,
            'member_id' => 99,
        ]);

        $listing->refresh();
        $this->assertEquals('Updated Title', $listing->title);
        $this->assertEquals(JobListingState::DRAFT, $listing->state);
        $this->assertNull($listing->approval_by);
        $this->assertNull($listing->approval_at);
        $this->assertEquals(0, $listing->view_count);
        $this->assertEquals($organization->id, $listing->organization_id);
        $this->assertEquals($member->id, $listing->member_id);
    }

    public function test_application_note_protects_authorship_from_mass_assignment(): void
    {
        $note = (new ApplicationNote())->fill([
            'body' => 'Internal note',
            'application_id' => 99,
            'author_user_id' => 99,
            'author_name_snapshot' => 'Spoofed Author',
        ]);

        $this->assertEquals('Internal note', $note->body);
        $this->assertNull($note->application_id, 'application_id must not be mass-assignable');
        $this->assertNull($note->author_user_id, 'author_user_id must not be mass-assignable');
        $this->assertNull($note->author_name_snapshot, 'author_name_snapshot must not be mass-assignable');
    }

    public function test_candidate_profile_protects_member_id_from_mass_assignment(): void
    {
        $member = $this->createMember();
        $profile = CandidateProfile::factory()->create(['member_id' => $member->id]);

        $profile->update([
            'headline' => 'Updated Headline',
            'member_id' => 99,
        ]);

        $profile->refresh();
        $this->assertEquals('Updated Headline', $profile->headline);
        $this->assertEquals($member->id, $profile->member_id);
    }

    public function test_work_experience_and_education_protect_candidate_profile_id(): void
    {
        $member = $this->createMember();
        $profile = CandidateProfile::factory()->create(['member_id' => $member->id]);

        $experience = WorkExperience::factory()->create(['candidate_profile_id' => $profile->id]);
        $education = Education::factory()->create(['candidate_profile_id' => $profile->id]);

        $experience->update(['company' => 'New Co', 'candidate_profile_id' => 99]);
        $education->update(['institution' => 'New Edu', 'candidate_profile_id' => 99]);

        $experience->refresh();
        $education->refresh();

        $this->assertEquals('New Co', $experience->company);
        $this->assertEquals($profile->id, $experience->candidate_profile_id);
        $this->assertEquals('New Edu', $education->institution);
        $this->assertEquals($profile->id, $education->candidate_profile_id);
    }

    private function createMember(): Member
    {
        $role = Role::firstOrCreate(
            ['name' => 'member'],
            ['title' => 'Member', 'is_active' => true, 'is_admin' => false, 'perm' => []]
        );

        return Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $role->id,
        ]);
    }
}
