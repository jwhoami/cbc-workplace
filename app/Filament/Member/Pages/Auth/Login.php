<?php

namespace App\Filament\Member\Pages\Auth;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as AuthLogin;
use Filament\Facades\Filament;

/**
 * @property ComponentContainer $form
 */
class Login extends AuthLogin
{

  protected static string $view = 'filament.member.pages.login';

  public function mount(): void
  {
    if (Filament::auth()->check()) {
      redirect(url(route('filament.member.pages.dashboard')));
    }

    $this->form->fill();
  }

  public function form(Form $form): Form
    {
        return $form
          ->schema([
            TextInput::make('email')
              ->label(__('login.fields.email.label'))
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

}
