<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Auth\EditProfile as AuthEditProfile;

class EditProfile extends AuthEditProfile
{
  protected static ?string $navigationIcon = 'heroicon-o-document-text';

  protected function getHeaderActions(): array
  {
    return [];
  }

  public static function isSimple(): bool
  {
    return false;
  }
}
