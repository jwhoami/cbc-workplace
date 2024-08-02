<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
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
          ->revealable()
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
