<?php

namespace Tests\Feature\Member\Actions;

use App\Enums\MembershipState;
use App\Filament\Member\Pages\Contact;
use App\Models\Member;
use App\Models\MemberContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests cover the `request-membership` action that lives on the Contact
 * page (moved from EditProfile in commit 4c87fdf). The action now calls
 * `Affiliate::run()` which auto-approves the member.
 */
class RequestMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = Member::factory()->create([
            'email' => 'member@gmail.com',
            'membership_state' => MembershipState::UNDEFINED,
        ]);
        // Contact page mount() reads the member's contact relation; seed it.
        MemberContact::query()->create([
            'member_id' => $this->member->id,
            'name' => $this->member->name,
            'email' => $this->member->email,
        ]);
        $this->member->refresh();

        Livewire::actingAs($this->member, 'member');
    }

    public function test_request_membership_action_is_present_on_contact_page(): void
    {
        Livewire::test(Contact::class)
            ->assertActionExists('request-membership');
    }

    public function test_request_membership_action_is_disabled_when_already_approved(): void
    {
        $this->member->forceFill(['membership_state' => MembershipState::APPROVED])->save();

        Livewire::test(Contact::class)
            ->assertActionDisabled('request-membership');
    }

    public function test_request_membership_action_is_enabled_when_not_yet_approved(): void
    {
        Livewire::test(Contact::class)
            ->assertActionEnabled('request-membership');
    }

    public function test_request_membership_action_auto_approves_via_affiliate(): void
    {
        Mail::fake();

        Livewire::test(Contact::class)
            ->callAction('request-membership');

        $this->member->refresh();
        $this->assertEquals(MembershipState::APPROVED, $this->member->membership_state);
    }
}
