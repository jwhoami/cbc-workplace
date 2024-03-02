<?php

use function Pest\Laravel\get;
use function Pest\Laravel\seed;
use function Pest\Livewire\livewire;
use App\Filament\Admin;
use App\Filament\Member;

describe('admin-login', function () {
  beforeEach(function () {
    seed();
  });

  it('can login to admin panel', function () {
    get('/admin/login');
    livewire(Admin\Pages\Auth\Login::class)
      ->fill(['data' => ['email' => 'admin', 'password' => 'password']])
      ->call('authenticate')
      ->assertHasNoErrors()
      ->assertRedirect('/admin');
  });

  it('cannot login to member panel', function () {
    get('/member/login');
    livewire(Member\Pages\Auth\Login::class)
      ->fill(['data' => ['email' => 'admin@gmail.com', 'password' => 'password']])
      ->call('authenticate')
      ->assertHasErrors();
  });
});
