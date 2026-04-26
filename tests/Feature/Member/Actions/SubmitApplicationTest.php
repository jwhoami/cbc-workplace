<?php

namespace Tests\Feature\Member\Actions;

use App\Actions\Member\SubmitApplication;
use App\Enums\ApplicationStatus;
use App\Enums\OrganizationVerificationState;
use App\Mail\Member\ApplicationSubmitted;
use App\Mail\Organization\ApplicationReceived;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SubmitApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected Member $candidate;

    protected Member $orgOwner;

    protected Organization $organization;

    protected JobListing $listing;

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

        $this->candidate = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $memberRole->id,
        ]);

        CandidateProfile::factory()->create([
            'member_id' => $this->candidate->id,
        ]);

        $this->orgOwner = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $memberRole->id,
        ]);

        $this->organization = Organization::factory()->create([
            'member_id' => $this->orgOwner->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);

        $this->listing = JobListing::factory()
            ->forOrganization($this->organization)
            ->active()
            ->create([
                'application_deadline' => now()->addMonth(),
                'screening_questions' => null,
            ]);

        $this->actingAs($this->candidate, 'member');
    }

    public function test_submits_application_with_cover_letter_and_persists_row(): void
    {
        Mail::fake();

        $application = SubmitApplication::run($this->candidate, $this->listing, [
            'cover_letter' => 'Quiero contribuir con esta organización.',
        ]);

        $this->assertInstanceOf(Application::class, $application);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'member_id' => $this->candidate->id,
            'job_listing_id' => $this->listing->id,
            'status' => ApplicationStatus::RECEIVED->value,
            'cover_letter' => 'Quiero contribuir con esta organización.',
            'candidate_name_snapshot' => $this->candidate->name,
            'candidate_email_snapshot' => $this->candidate->email,
        ]);
    }

    public function test_sends_confirmation_email_to_candidate_and_notification_to_organization(): void
    {
        Mail::fake();

        SubmitApplication::run($this->candidate, $this->listing, []);

        Mail::assertSent(ApplicationSubmitted::class, function ($mail) {
            return $mail->hasTo($this->candidate->email);
        });

        Mail::assertSent(ApplicationReceived::class, function ($mail) {
            return $mail->hasTo($this->orgOwner->email);
        });
    }

    public function test_writes_activity_log_entry_on_create(): void
    {
        Mail::fake();

        $application = SubmitApplication::run($this->candidate, $this->listing, []);

        $this->assertTrue(
            Activity::query()
                ->where('event', 'application.create')
                ->where('subject_type', Application::class)
                ->where('subject_id', $application->id)
                ->exists()
        );
    }

    public function test_fails_when_member_has_no_candidate_profile(): void
    {
        Mail::fake();

        $member = Member::factory()->create([
            'is_active' => true,
            'is_blocked' => false,
            'role_id' => $this->candidate->role_id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application.notifications.no_profile'));

        SubmitApplication::run($member, $this->listing, []);
    }

    public function test_fails_when_listing_is_not_active(): void
    {
        Mail::fake();

        $draft = JobListing::factory()
            ->forOrganization($this->organization)
            ->draft()
            ->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application.notifications.listing_inactive'));

        SubmitApplication::run($this->candidate, $draft, []);
    }

    public function test_fails_when_listing_deadline_has_passed(): void
    {
        Mail::fake();

        $expired = JobListing::factory()
            ->forOrganization($this->organization)
            ->active()
            ->create([
                'application_deadline' => now()->subDay(),
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application.notifications.listing_inactive'));

        SubmitApplication::run($this->candidate, $expired, []);
    }

    public function test_fails_on_duplicate_submission(): void
    {
        Mail::fake();

        SubmitApplication::run($this->candidate, $this->listing, []);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application.notifications.duplicate'));

        SubmitApplication::run($this->candidate, $this->listing, []);
    }

    public function test_fails_when_required_screening_question_is_unanswered(): void
    {
        Mail::fake();

        $listing = JobListing::factory()
            ->forOrganization($this->organization)
            ->active()
            ->create([
                'application_deadline' => now()->addMonth(),
                'screening_questions' => ['¿Cuántos años de experiencia tiene?'],
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application.validation.answer_required'));

        SubmitApplication::run($this->candidate, $listing, [
            'screening_answers' => [],
        ]);
    }

    public function test_persists_screening_answers_when_provided(): void
    {
        Mail::fake();

        $listing = JobListing::factory()
            ->forOrganization($this->organization)
            ->active()
            ->create([
                'application_deadline' => now()->addMonth(),
                'screening_questions' => ['¿Cuántos años de experiencia tiene?'],
            ]);

        $application = SubmitApplication::run($this->candidate, $listing, [
            'screening_answers' => [
                ['question' => '¿Cuántos años de experiencia tiene?', 'answer' => '5 años'],
            ],
        ]);

        $this->assertSame('5 años', $application->screening_answers[0]['answer']);
    }

    public function test_copies_cv_snapshot_when_candidate_has_cv(): void
    {
        Mail::fake();
        Storage::fake('public');

        $profile = $this->candidate->candidateProfile;
        $cvPath = "candidates/cvs/cv-{$this->candidate->id}.pdf";
        Storage::disk('public')->put($cvPath, 'Original CV content');
        $profile->update(['cv_path' => $cvPath]);

        $application = SubmitApplication::run($this->candidate, $this->listing, []);

        $expected = "applications/{$application->id}/cv.pdf";
        $application->refresh();
        $this->assertSame($expected, $application->cv_snapshot_path);
        $this->assertSame("cv-{$this->candidate->id}.pdf", $application->cv_snapshot_filename);
        Storage::disk('public')->assertExists($expected);
    }

    public function test_cv_snapshot_is_immutable_after_profile_cv_changes(): void
    {
        Mail::fake();
        Storage::fake('public');

        $profile = $this->candidate->candidateProfile;
        $original = "candidates/cvs/cv-{$this->candidate->id}.pdf";
        Storage::disk('public')->put($original, 'Original CV content');
        $profile->update(['cv_path' => $original]);

        $application = SubmitApplication::run($this->candidate, $this->listing, []);
        $application->refresh();
        $snapshotPath = $application->cv_snapshot_path;
        $this->assertNotNull($snapshotPath);
        $snapshotContent = Storage::disk('public')->get($snapshotPath);

        $newPath = "candidates/cvs/cv-{$this->candidate->id}-v2.pdf";
        Storage::disk('public')->put($newPath, 'Updated CV content');
        $profile->update(['cv_path' => $newPath]);

        $application->refresh();
        $this->assertSame($snapshotPath, $application->cv_snapshot_path);
        $this->assertSame($snapshotContent, Storage::disk('public')->get($snapshotPath));
        $this->assertSame('Original CV content', Storage::disk('public')->get($snapshotPath));
    }
}
