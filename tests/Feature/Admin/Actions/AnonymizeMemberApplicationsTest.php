<?php

namespace Tests\Feature\Admin\Actions;

use App\Actions\Admin\AnonymizeMemberApplications;
use App\Enums\OrganizationVerificationState;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AnonymizeMemberApplicationsTest extends TestCase
{
    use RefreshDatabase;

    protected Member $candidate;

    protected Member $orgOwner;

    protected JobListing $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => 'member',
            'title' => 'Member',
            'is_active' => true,
            'is_admin' => false,
            'perm' => [],
        ]);

        $this->candidate = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $role->id,
        ]);
        CandidateProfile::factory()->create(['member_id' => $this->candidate->id]);

        $this->orgOwner = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $role->id,
        ]);

        $org = Organization::factory()->create([
            'member_id' => $this->orgOwner->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);

        $this->listing = JobListing::factory()
            ->forOrganization($org)
            ->active()
            ->create([
                'application_deadline' => now()->addMonth(),
                'screening_questions' => null,
            ]);
    }

    public function test_anonymizes_all_member_applications(): void
    {
        $apps = Application::factory()->count(3)->received()->sequence(
            fn ($seq) => ['job_listing_id' => JobListing::factory()->forOrganization($this->listing->organization)->active()->create(['application_deadline' => now()->addMonth(), 'screening_questions' => null])->id]
        )->create([
            'member_id' => $this->candidate->id,
            'candidate_name_snapshot' => 'Juan Hooper',
            'candidate_email_snapshot' => 'juan@test.local',
        ]);

        $count = AnonymizeMemberApplications::run($this->candidate);

        $this->assertEquals(3, $count);

        foreach ($apps as $app) {
            $app->refresh();
            $this->assertEquals(__('models/application.snapshot.anonymized_name'), $app->candidate_name_snapshot);
            $this->assertNull($app->candidate_email_snapshot);
            $this->assertNull($app->cv_snapshot_path);
            $this->assertNull($app->cv_snapshot_filename);
            $this->assertNull($app->member_id);
            $this->assertNotNull($app->anonymized_at);
        }
    }

    public function test_deletes_cv_files_from_disk(): void
    {
        Storage::fake('public');
        $cvPath = 'applications/123/cv.pdf';
        Storage::disk('public')->put($cvPath, 'Original CV');

        $app = Application::factory()->received()->create([
            'member_id' => $this->candidate->id,
            'job_listing_id' => $this->listing->id,
            'cv_snapshot_path' => $cvPath,
            'cv_snapshot_filename' => 'cv.pdf',
        ]);

        AnonymizeMemberApplications::run($this->candidate);

        Storage::disk('public')->assertMissing($cvPath);
    }

    public function test_writes_one_activity_log_entry_per_application(): void
    {
        Application::factory()->count(2)->received()->sequence(
            fn ($seq) => ['job_listing_id' => JobListing::factory()->forOrganization($this->listing->organization)->active()->create(['application_deadline' => now()->addMonth(), 'screening_questions' => null])->id]
        )->create(['member_id' => $this->candidate->id]);

        AnonymizeMemberApplications::run($this->candidate);

        $logs = Activity::where('event', 'application.anonymize')
            ->where('subject_type', Application::class)
            ->count();

        $this->assertEquals(2, $logs);
    }

    public function test_is_idempotent(): void
    {
        Application::factory()->received()->create([
            'member_id' => $this->candidate->id,
            'job_listing_id' => $this->listing->id,
        ]);

        $first = AnonymizeMemberApplications::run($this->candidate);
        $second = AnonymizeMemberApplications::run($this->candidate);

        $this->assertEquals(1, $first);
        $this->assertEquals(0, $second, 'Already-anonymized rows must not be re-processed');
        $this->assertEquals(1, Activity::where('event', 'application.anonymize')->count());
    }

    public function test_member_can_be_force_deleted_after_anonymization(): void
    {
        Application::factory()->received()->create([
            'member_id' => $this->candidate->id,
            'job_listing_id' => $this->listing->id,
        ]);

        $candidateId = $this->candidate->id;

        // The Member::deleting hook runs AnonymizeMemberApplications automatically.
        $this->candidate->delete();

        $this->assertDatabaseMissing('members', ['id' => $candidateId]);
        $this->assertDatabaseHas('applications', [
            'member_id' => null,
            'candidate_name_snapshot' => __('models/application.snapshot.anonymized_name'),
        ]);
    }

    public function test_returns_zero_for_member_with_no_applications(): void
    {
        $count = AnonymizeMemberApplications::run($this->candidate);

        $this->assertEquals(0, $count);
        $this->assertEquals(0, Activity::where('event', 'application.anonymize')->count());
    }
}
