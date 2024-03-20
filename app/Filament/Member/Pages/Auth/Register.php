<?php

namespace App\Filament\Member\Pages\Auth;

use Filament\Pages\Auth\Register as AuthRegister;
use Filament\Forms;
use Illuminate\Validation\Rules\Password;

class Register extends AuthRegister
{
  protected function getPasswordFormComponent(): Forms\Components\Component
  {
    return Forms\Components\TextInput::make('password')
      ->label(__('filament-panels::pages/auth/register.form.password.label'))
      ->password()
      ->revealable()
      ->required()
      ->rule(Password::default())
      ->same('passwordConfirmation')
      ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
  }
}
