<?php

use App\Filament\Member\Pages\EditProfile;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

describe('member-profile', function () {
  it('renders profile page', function () {
    get('/member/profile')
      ->assertSuccessful(EditProfile::class);
  });

  it('can update its profile', function () {
    livewire(EditProfile::class)
      ->fill(['data' => ['email' => 'new@gmail.com']])
      ->call('save')
      ->assertHasNoErrors();
  });
});
