<?php

namespace Tests\Feature\Member\Actions;

use App\Actions\Member\UpdateApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Enums\OrganizationVerificationState;
use App\Mail\Member\ApplicationStatusChanged;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UpdateApplicationStatusTest extends TestCase
{
    use RefreshDatabase;

    protected Member $candidate;

    protected Member $orgOwner;

    protected Organization $organization;

    protected JobListing $listing;

    protected Application $application;

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

        $this->application = Application::factory()->received()->create([
            'member_id' => $this->candidate->id,
            'job_listing_id' => $this->listing->id,
            'candidate_profile_id' => $this->candidate->candidateProfile->id,
            'candidate_name_snapshot' => $this->candidate->name,
            'candidate_email_snapshot' => $this->candidate->email,
        ]);

        $this->actingAs($this->orgOwner, 'member');
    }

    public function test_received_can_advance_to_in_review(): void
    {
        Mail::fake();

        UpdateApplicationStatus::run($this->application, ApplicationStatus::IN_REVIEW);

        $this->application->refresh();
        $this->assertEquals(ApplicationStatus::IN_REVIEW, $this->application->status);
        $this->assertNotNull($this->application->last_status_changed_at);
        $this->assertEquals($this->orgOwner->name, $this->application->last_status_changed_by);
    }

    public function test_received_can_skip_directly_to_rejected(): void
    {
        Mail::fake();

        UpdateApplicationStatus::run($this->application, ApplicationStatus::REJECTED);

        $this->application->refresh();
        $this->assertEquals(ApplicationStatus::REJECTED, $this->application->status);
    }

    public function test_received_can_skip_directly_to_accepted(): void
    {
        Mail::fake();

        UpdateApplicationStatus::run($this->application, ApplicationStatus::ACCEPTED);

        $this->application->refresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $this->application->status);
    }

    public function test_in_review_can_skip_interview_and_go_to_accepted(): void
    {
        Mail::fake();
        $this->application->forceFill(['status' => ApplicationStatus::IN_REVIEW])->save();

        UpdateApplicationStatus::run($this->application, ApplicationStatus::ACCEPTED);

        $this->application->refresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $this->application->status);
    }

    public function test_sends_status_change_email_to_candidate(): void
    {
        Mail::fake();

        UpdateApplicationStatus::run($this->application, ApplicationStatus::IN_REVIEW);

        Mail::assertSent(ApplicationStatusChanged::class, function ($mail) {
            return $mail->hasTo($this->candidate->email)
                && $mail->current === ApplicationStatus::IN_REVIEW
                && $mail->previous === ApplicationStatus::RECEIVED;
        });
    }

    public function test_writes_activity_log_with_from_and_to_properties(): void
    {
        Mail::fake();

        UpdateApplicationStatus::run($this->application, ApplicationStatus::INTERVIEW);

        $log = Activity::query()
            ->where('event', 'application.status-change')
            ->where('subject_type', Application::class)
            ->where('subject_id', $this->application->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ApplicationStatus::RECEIVED->value, $log->properties['from']);
        $this->assertEquals(ApplicationStatus::INTERVIEW->value, $log->properties['to']);
    }

    public function test_rejects_backwards_transition(): void
    {
        Mail::fake();
        $this->application->forceFill(['status' => ApplicationStatus::INTERVIEW])->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application.notifications.invalid_transition'));

        UpdateApplicationStatus::run($this->application, ApplicationStatus::IN_REVIEW);

        Mail::assertNothingSent();
    }

    public function test_rejects_transition_back_to_received(): void
    {
        Mail::fake();
        $this->application->forceFill(['status' => ApplicationStatus::IN_REVIEW])->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application.notifications.invalid_transition'));

        UpdateApplicationStatus::run($this->application, ApplicationStatus::RECEIVED);
    }

    public function test_rejects_transition_out_of_terminal_rejected(): void
    {
        Mail::fake();
        $this->application->forceFill(['status' => ApplicationStatus::REJECTED])->save();

        $this->expectException(\Exception::class);

        UpdateApplicationStatus::run($this->application, ApplicationStatus::ACCEPTED);
    }

    public function test_rejects_transition_out_of_terminal_accepted(): void
    {
        Mail::fake();
        $this->application->forceFill(['status' => ApplicationStatus::ACCEPTED])->save();

        $this->expectException(\Exception::class);

        UpdateApplicationStatus::run($this->application, ApplicationStatus::REJECTED);
    }

    public function test_invalid_transition_does_not_send_email_or_log(): void
    {
        Mail::fake();
        $this->application->forceFill(['status' => ApplicationStatus::REJECTED])->save();
        $beforeLogs = Activity::where('event', 'application.status-change')->count();

        try {
            UpdateApplicationStatus::run($this->application, ApplicationStatus::ACCEPTED);
        } catch (\Exception $e) {
            // expected
        }

        Mail::assertNothingSent();
        $this->assertEquals($beforeLogs, Activity::where('event', 'application.status-change')->count());
    }

    public function test_writes_comment_on_status_change(): void
    {
        Mail::fake();
        $beforeComments = $this->application->comments()->count();

        UpdateApplicationStatus::run($this->application, ApplicationStatus::IN_REVIEW);

        $this->assertEquals($beforeComments + 1, $this->application->fresh()->comments()->count());
    }
}
