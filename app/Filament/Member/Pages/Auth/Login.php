<?php

namespace App\Filament\Member\Pages\Auth;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as AuthLogin;

/**
 * @property ComponentContainer $form
 */
class Login extends AuthLogin
{
  public function form(Form $form): Form
  {
    return $form
      ->schema([
        TextInput::make('email')
          ->label(__('login.fields.username.label'))
          ->required()
          ->autocomplete(),
        TextInput::make('password')
          ->label(__('filament-panels::pages/auth/login.form.password.label'))
          ->password()
          ->required(),
        //      Captcha::make('captcha')
        //        ->autocomplete('off'),
        Checkbox::make('remember')
          ->label(__('filament-panels::pages/auth/login.form.remember.label')),
      ])->statePath('data');
  }

  protected function getCredentialsFromFormData(array $data): array
  {
    return [
      'username' => $data['email'],
      'password' => $data['password'],
    ];
  }
}
