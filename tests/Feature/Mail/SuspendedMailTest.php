<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\Organization\Suspended;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class SuspendedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_t1_body_does_not_contain_suspension_reason(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verifiedSuspended('Sensitive internal note 9876', 'Admin')->create([
            'member_id' => $member->id,
        ]);

        $mail = new Suspended($org);

        $html = $mail->render();
        $this->assertStringNotContainsString('Sensitive internal note 9876', $html);
    }

    public function test_t2_renders_without_errors_when_no_reason_is_set(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verified()->create(['member_id' => $member->id]);

        $mail = new Suspended($org);

        $this->assertIsString($mail->render());
    }

    public function test_t3_subject_resolves_to_translation_key(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verifiedSuspended()->create(['member_id' => $member->id]);

        $envelope = (new Suspended($org))->envelope();

        $this->assertSame(__('mail/organization/suspended.subject'), $envelope->subject);
    }

    public function test_t4_implements_should_queue(): void
    {
        $member = Member::factory()->create();
        $org = Organization::factory()->verifiedSuspended()->create(['member_id' => $member->id]);

        $this->assertInstanceOf(ShouldQueue::class, new Suspended($org));
    }

    public function test_t5_constructor_takes_exactly_one_argument(): void
    {
        $reflection = new ReflectionClass(Suspended::class);

        $this->assertSame(1, $reflection->getConstructor()->getNumberOfParameters());
    }
}
