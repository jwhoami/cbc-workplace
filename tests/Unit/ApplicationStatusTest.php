<?php

namespace Tests\Unit;

use App\Enums\ApplicationStatus;
use Tests\TestCase;

class ApplicationStatusTest extends TestCase
{
    public function test_received_is_not_terminal(): void
    {
        $this->assertFalse(ApplicationStatus::RECEIVED->isTerminal());
    }

    public function test_in_review_is_not_terminal(): void
    {
        $this->assertFalse(ApplicationStatus::IN_REVIEW->isTerminal());
    }

    public function test_interview_is_not_terminal(): void
    {
        $this->assertFalse(ApplicationStatus::INTERVIEW->isTerminal());
    }

    public function test_rejected_is_terminal(): void
    {
        $this->assertTrue(ApplicationStatus::REJECTED->isTerminal());
    }

    public function test_accepted_is_terminal(): void
    {
        $this->assertTrue(ApplicationStatus::ACCEPTED->isTerminal());
    }

    public function test_received_can_advance_to_any_later_state(): void
    {
        $from = ApplicationStatus::RECEIVED;
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::IN_REVIEW));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::INTERVIEW));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::REJECTED));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::ACCEPTED));
    }

    public function test_in_review_can_advance_to_any_later_state(): void
    {
        $from = ApplicationStatus::IN_REVIEW;
        $this->assertFalse($from->canTransitionTo(ApplicationStatus::RECEIVED));
        $this->assertFalse($from->canTransitionTo(ApplicationStatus::IN_REVIEW));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::INTERVIEW));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::REJECTED));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::ACCEPTED));
    }

    public function test_interview_can_only_advance_to_terminal_states(): void
    {
        $from = ApplicationStatus::INTERVIEW;
        $this->assertFalse($from->canTransitionTo(ApplicationStatus::RECEIVED));
        $this->assertFalse($from->canTransitionTo(ApplicationStatus::IN_REVIEW));
        $this->assertFalse($from->canTransitionTo(ApplicationStatus::INTERVIEW));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::REJECTED));
        $this->assertTrue($from->canTransitionTo(ApplicationStatus::ACCEPTED));
    }

    public function test_rejected_cannot_transition_anywhere(): void
    {
        $from = ApplicationStatus::REJECTED;
        foreach (ApplicationStatus::cases() as $next) {
            $this->assertFalse(
                $from->canTransitionTo($next),
                "REJECTED must not transition to {$next->name}"
            );
        }
    }

    public function test_accepted_cannot_transition_anywhere(): void
    {
        $from = ApplicationStatus::ACCEPTED;
        foreach (ApplicationStatus::cases() as $next) {
            $this->assertFalse(
                $from->canTransitionTo($next),
                "ACCEPTED must not transition to {$next->name}"
            );
        }
    }

    public function test_no_state_can_regress_to_received(): void
    {
        foreach (ApplicationStatus::cases() as $from) {
            $this->assertFalse(
                $from->canTransitionTo(ApplicationStatus::RECEIVED),
                "{$from->name} must not transition back to RECEIVED"
            );
        }
    }
}
