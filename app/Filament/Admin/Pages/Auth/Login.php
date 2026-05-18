<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as AuthLogin;
use Illuminate\Validation\ValidationException;
use MarcoGermani87\FilamentCaptcha\Forms\Components\CaptchaField;

/**
 * @property ComponentContainer $form
 */
class Login extends AuthLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username')
                    ->label(__('login.fields.username.label'))
                    ->required()
                    ->autocomplete('username'),
                TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/login.form.password.label'))
                    ->password()
                    ->revealable()
                    ->required(),
                //      Captcha::make('captcha')
                //        ->autocomplete('off'),
                ...(app()->environment(['testing', 'local']) ? [] : [
                    CaptchaField::make('captcha'),
                ]),
                Checkbox::make('remember')
                    ->label(__('filament-panels::pages/auth/login.form.remember.label')),
            ])->statePath('data');
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
