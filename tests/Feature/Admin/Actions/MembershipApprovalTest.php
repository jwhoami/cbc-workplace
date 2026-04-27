<?php

namespace Tests\Feature\Admin\Actions;

use App\Enums\MembershipState;
use App\Enums\MemberType;
use App\Filament\Admin\Resources\MemberResource\Pages\ViewMember;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MembershipApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = Member::factory()->create([
            'email' => 'member@gmail.com',
            'type' => MemberType::VISITOR,
            'membership_state' => MembershipState::PENDING,
            'membership_reason' => 'reason',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'title' => 'Admin',
            'is_active' => true,
            'is_admin' => true,
            'perm' => [],
        ]);
        Livewire::actingAs(User::factory()->create(['role_id' => $adminRole->id]), 'admin');
        $this->get('/admin');
    }

    public function test_it_can_approve_request(): void
    {
        Livewire::test(ViewMember::class, ['record' => $this->member->id])
            ->callAction('membership-approval', data: [
                'decision' => MembershipState::APPROVED->value,
                'membership_approval_reason' => 'Good Job',
            ])
            ->assertHasNoActionErrors();

        $member = $this->member->fresh();
        $this->assertEquals($member->type, MemberType::MEMBER);
        $this->assertEquals($member->membership_state, MembershipState::APPROVED);
        $this->assertEquals($member->membership_approval_reason, 'Good Job');
        $this->assertEquals(auth()->user()->name, $member->membership_approval_by);
        $this->assertNotNull($member->membership_approval_at);
    }

    public function test_it_can_reject_request(): void
    {
        Livewire::test(ViewMember::class, ['record' => $this->member->id])
            ->callAction('membership-approval', data: [
                'decision' => MembershipState::REJECTED->value,
                'membership_approval_reason' => 'Bad Job',
            ])
            ->assertHasNoActionErrors();

        $member = $this->member->fresh();
        $this->assertEquals($member->type, MemberType::VISITOR);
        $this->assertEquals($member->membership_state, MembershipState::REJECTED);
        $this->assertEquals($member->membership_approval_reason, 'Bad Job');
        $this->assertEquals(auth()->user()->name, $member->membership_approval_by);
        $this->assertNotNull($member->membership_approval_at);
    }
}
