<?php

namespace App\Filament\Member\Pages\Auth;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as AuthLogin;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;

/**
 * @property ComponentContainer $form
 */
class Login extends AuthLogin
{
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
