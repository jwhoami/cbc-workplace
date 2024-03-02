<?php

use function Pest\Laravel\get;
use function Pest\Laravel\seed;
use function Pest\Livewire\livewire;
use App\Filament\Admin as AdminPanel;
use App\Filament\Member as MemberPanel;
use App\Models\Member;

describe('member-login', function () {
  beforeEach(function () {
    seed();
  });

  it('can login to member panel', function () {
    get('/member/login');
    livewire(MemberPanel\Pages\Auth\Login::class)
      ->fill(['data' => ['email' => 'member@gmail.com', 'password' => 'password']])
      ->call('authenticate')
      ->assertHasNoErrors()
      ->assertRedirect('/member');
  });

  it('cannnot login to admin panel', function () {
    get('/admin/login');
    livewire(AdminPanel\Pages\Auth\Login::class)
      ->fill(['data' => ['email' => 'member@gmail.com', 'password' => 'password']])
      ->call('authenticate')
      ->assertHasErrors();
  });
});

describe('member-register', function () {
  it('redirects to /member panel dashboard after registering', function () {
    get('/member/register');
    livewire(MemberPanel\Pages\Auth\Register::class)
      ->fill(['data' => [
        'name' => 'Member',
        'email' => 'member@gmail.com',
        'password' => 'password',
        'passwordConfirmation' => 'password',
      ]])
      ->call('register')
      ->assertHasNoErrors()
      ->assertRedirect('/member');
  });

  it('creates new record after registering', function () {
    get('/member/register');
    livewire(MemberPanel\Pages\Auth\Register::class)
      ->fill(['data' => [
        'name' => 'Member',
        'email' => 'member@gmail.com',
        'password' => 'password',
        'passwordConfirmation' => 'password',
      ]])
      ->call('register');

    $member = Member::query()->where('email', 'member@gmail.com')->first();
    expect($member)->not->toBeNull();
  });

  it('throws a validation error if email is already taken', function () {
    Member::factory()->create(['email' => 'member@gmail.com']);

    get('/member/register');
    livewire(MemberPanel\Pages\Auth\Register::class)
      ->fill(['data' => [
        'name' => 'Member',
        'email' => 'member@gmail.com',
        'password' => 'password',
        'passwordConfirmation' => 'password',
      ]])
      ->call('register')
      ->assertHasErrors();
  });
});
