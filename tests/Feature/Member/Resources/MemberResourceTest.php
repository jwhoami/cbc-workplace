<?php

namespace Tests\Feature\Member\Resources;

use App\Enums\MemberType;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class MemberResourceTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    $member = Member::factory()->create(['type' => MemberType::MEMBER]);

    Livewire::actingAs($member, 'member');
    $this->get('/member');
  }

  public function test_it_can_list(): void
  {
    $this->get('/member/posts')
      ->assertSuccessful();
  }
}
