<?php

namespace Tests\Feature\Member\Auth;

use App\Filament\Member\Pages\Auth\Login;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  protected Member $member;

  protected function setUp(): void
  {
    parent::setUp();

    $this->member = Member::factory()->create([
      'email' => 'member@gmail.com',
      'password'  => 'password'
    ]);
    $this->get('/member');
  }

  public function test_it_renders(): void
  {
    $this->get('/member/login')
      ->assertSeeLivewire(Login::class);
  }

  public function test_it_allows_members_to_login(): void
  {
    Livewire::test(Login::class)
      ->fill(['data' => [
        'email' => 'member@gmail.com',
        'password' => 'password',
      ]])
      ->call('authenticate')
      ->assertHasNoErrors()
      ->assertRedirect('/member');
  }

  public function test_it_throws_error_if_credentials_are_invalid(): void
  {
    Livewire::test(Login::class)
      ->fill(['data' => [
        'email' => 'member@gmail.com',
        'password' => 'assword',
      ]])
      ->call('authenticate')
      ->assertHasErrors('data.email');
  }

  public function test_it_rejects_admin_users()
  {
    User::factory()->create(['username' => 'admin', 'email' => 'admin@gmail.com']);

    Livewire::test(Login::class)
      ->fill(['data' => [
        'email' => 'admin@gmail.com',
        'password' => 'password',
      ]])
      ->call('authenticate')
      ->assertHasErrors('data.email');
  }
}
