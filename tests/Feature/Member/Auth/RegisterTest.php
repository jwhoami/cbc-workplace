<?php

namespace Tests\Feature\Member\Auth;

use App\Enums\MemberType;
use App\Filament\Member\Pages\Auth\Register;
use App\Filament\Member\Pages\Dashboard;
use App\Models\Member;
use Filament\Notifications\Auth\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    $this->get('/member');
  }

  public function test_it_renders(): void
  {
    $this->get('/member/register')
      ->assertSeeLivewire(Register::class);
  }

  public function test_it_creates_new_member_as_visitor(): void
  {
    Livewire::test(Register::class)
      ->fill(['data' => [
        'email' => 'member@gmail.com',
        'name' => 'Member',
        'password' => 'password',
        'passwordConfirmation' => 'password'
      ]])
      ->call('register')
      ->assertHasNoErrors();

    $member = Member::query()->where('email', 'member@gmail.com')->first();
    $this->assertEquals($member->type, MemberType::VISITOR);
  }

  public function test_it_prompts_member_to_verify_his_email(): void
  {
    Mail::fake();

    Livewire::test(Register::class)
      ->fill(['data' => [
        'email' => 'member@gmail.com',
        'name' => 'Member',
        'password' => 'password',
        'passwordConfirmation' => 'password'
      ]])
      ->call('register')
      ->assertHasNoErrors();

    $this->get('/member')
      ->assertRedirect('/member/email-verification/prompt');
  }

  public function test_it_rejects_duplicate_emails(): void
  {
    Member::factory()->create(['email' => 'member@gmail.com']);

    Livewire::test(Register::class)
      ->fill(['data' => [
        'email' => 'member@gmail.com',
        'name' => 'Member',
        'password' => 'password',
        'passwordConfirmation' => 'password'
      ]])
      ->call('register')
      ->assertHasErrors('data.email');
  }
}
