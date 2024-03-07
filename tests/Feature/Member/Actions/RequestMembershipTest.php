<?php

namespace Tests\Feature\Member\Actions;

use App\Enums\MembershipState;
use App\Filament\Member\Pages\EditProfile;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RequestMembershipTest extends TestCase
{
  use RefreshDatabase;

  protected Member $member;

  protected function setUp(): void
  {
    parent::setUp();

    $this->member = Member::factory()->create([
      'email' => 'member@gmail.com',
      'membership_state' => MembershipState::UNDEFINED
    ]);
    Livewire::actingAs($this->member, 'member');
    $this->get('/member');
  }

  public function test_it_renders(): void
  {
    Livewire::test(EditProfile::class)
      ->assertActionExists('request-membership');
  }

  #[DataProvider('membershipStates')]
  public function test_it_is_visible_if_membership_state_is_undefined_or_rejected(MembershipState $membership): void
  {
    $this->member->membership_state = $membership;
    $this->member->save();

    $livewire = Livewire::test(EditProfile::class);
    $membership === MembershipState::UNDEFINED
      ? $livewire->assertActionVisible('request-membership')
      : $livewire->assertActionHidden('request-membership');
  }

  public function test_it_sets_its_membership_state_to_pending_and_adds_his_reason()
  {
    $reason =  'Important';
    Livewire::test(EditProfile::class)
      ->callAction('request-membership', data: [
        'reason' => $reason
      ]);

    $member = $this->member->fresh();
    $this->assertEquals($member->membership_state, MembershipState::PENDING);
    $this->assertEquals($member->membership_reason, $reason);
  }

  public static function membershipStates()
  {
    return [
      'undefined' => [MembershipState::UNDEFINED],
      'pending' => [MembershipState::PENDING],
      'approved' => [MembershipState::APPROVED],
      'rejected' => [MembershipState::REJECTED],
    ];
  }
}
