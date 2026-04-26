<?php

namespace Tests\Feature\Member\Actions;

use App\Actions\Member\AddApplicationNote;
use App\Actions\Member\DeleteApplicationNote;
use App\Actions\Member\UpdateApplicationNote;
use App\Enums\OrganizationVerificationState;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ApplicationNotesTest extends TestCase
{
    use RefreshDatabase;

    protected Member $candidate;

    protected Member $orgOwner;

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

        $org = Organization::factory()->create([
            'member_id' => $this->orgOwner->id,
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verified_at' => now(),
        ]);

        $listing = JobListing::factory()
            ->forOrganization($org)
            ->active()
            ->create([
                'application_deadline' => now()->addMonth(),
                'screening_questions' => null,
            ]);

        $this->application = Application::factory()->received()->create([
            'member_id' => $this->candidate->id,
            'job_listing_id' => $listing->id,
            'candidate_name_snapshot' => $this->candidate->name,
            'candidate_email_snapshot' => $this->candidate->email,
        ]);

        $this->actingAs($this->orgOwner, 'member');
    }

    public function test_can_add_a_note_with_author_snapshot(): void
    {
        $note = AddApplicationNote::run($this->application, 'El postulante tiene experiencia relevante.');

        $this->assertInstanceOf(ApplicationNote::class, $note);
        $this->assertEquals($this->application->id, $note->application_id);
        $this->assertEquals('El postulante tiene experiencia relevante.', $note->body);
        $this->assertEquals($this->orgOwner->name, $note->author_name_snapshot);
        $this->assertNull($note->author_user_id, 'Member-authored notes leave author_user_id NULL');
    }

    public function test_add_note_writes_activity_log(): void
    {
        $note = AddApplicationNote::run($this->application, 'Nota de prueba');

        $this->assertTrue(
            Activity::query()
                ->where('event', 'application-note.create')
                ->where('subject_type', ApplicationNote::class)
                ->where('subject_id', $note->id)
                ->exists()
        );
    }

    public function test_add_note_rejects_empty_body(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application-note.validation.body_required'));

        AddApplicationNote::run($this->application, '   ');
    }

    public function test_add_note_rejects_body_exceeding_2000_chars(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application-note.validation.body_max'));

        AddApplicationNote::run($this->application, str_repeat('x', 2001));
    }

    public function test_can_update_a_note_and_logs_change(): void
    {
        $note = AddApplicationNote::run($this->application, 'original');
        $beforeLogs = Activity::where('event', 'application-note.update')->count();

        UpdateApplicationNote::run($note, 'updated body');

        $note->refresh();
        $this->assertEquals('updated body', $note->body);
        $this->assertEquals($beforeLogs + 1, Activity::where('event', 'application-note.update')->count());
    }

    public function test_update_note_rejects_empty_body(): void
    {
        $note = AddApplicationNote::run($this->application, 'original');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('models/application-note.validation.body_required'));

        UpdateApplicationNote::run($note, '');
    }

    public function test_can_delete_a_note_with_log_before_removal(): void
    {
        $note = AddApplicationNote::run($this->application, 'to be deleted');
        $noteId = $note->id;

        DeleteApplicationNote::run($note);

        $this->assertDatabaseMissing('application_notes', ['id' => $noteId]);
        $this->assertTrue(
            Activity::query()
                ->where('event', 'application-note.delete')
                ->where('subject_id', $noteId)
                ->exists(),
            'Delete log must reference the note id, written BEFORE delete'
        );
    }

}
