<?php

namespace Tests\Feature\Admin\Auth;

use App\Filament\Admin\Pages\Auth\Login;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    $this->get('/admin');
  }

  public function test_it_renders(): void
  {
    $this->get('/admin/login')
      ->assertSeeLivewire(Login::class);
  }

  public function test_it_allows_admins_to_login(): void
  {
    User::factory()->create([
      'username' => 'admin',
      'password'  => 'password'
    ]);

    Livewire::test(Login::class)
      ->fill(['data' => [
        'email' => 'admin',
        'password' => 'password',
      ]])
      ->call('authenticate')
      ->assertHasNoErrors()
      ->assertRedirect('/admin');
  }

  public function test_it_throws_error_if_credentials_are_invalid(): void
  {
    Livewire::test(Login::class)
      ->fill(['data' => [
        'email' => 'admin',
        'password' => 'assword',
      ]])
      ->call('authenticate')
      ->assertHasErrors('data.email');
  }

  public function test_it_rejects_members()
  {
    Member::factory()->create(['email' => 'member@gmail.com', 'password' => 'password']);

    Livewire::test(Login::class)
      ->fill(['data' => [
        'email' => 'member@gmail.com',
        'password' => 'password',
      ]])
      ->call('authenticate')
      ->assertHasErrors('data.email');
  }
}

