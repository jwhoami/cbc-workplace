<?php

namespace App\Filament\Member\Pages\Auth;

use App\Models\Config;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as AuthLogin;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use MarcoGermani87\FilamentCaptcha\Forms\Components\CaptchaField;

/**
 * @property ComponentContainer $form
 */
class Login extends AuthLogin
{
    // protected static string $view = 'filament.member.pages.login';

    public function mount(): void
    {
        try {
            $this->rateLimit(Config::make()->getp('rateLimiter.login'));
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return;
        }

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            fn (): View => view('filament.member.login-page-footer-links'),
        );

        if (request()->has('redirect')) {
            session(['login_redirect' => request()->query('redirect')]);
        }

        if (Filament::auth()->check()) {
            if (session()->has('login_redirect')) {
                $redirect = session()->pull('login_redirect');
                if (str_starts_with($redirect, url('/')) || str_starts_with($redirect, '/')) {
                    redirect($redirect);
                    return;
                }
            }
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
                    ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
                    ->password()
                    ->revealable()
                    ->required(),
                ...(app()->environment(['testing', 'local']) ? [] : [
                    CaptchaField::make('captcha')
                        ->helperText(__('Acepta los caracteres sin importar mayúscula o minúscula')),
                ]),

                //      Captcha::make('captcha')
                //        ->autocomplete('off'),
                Checkbox::make('remember')
                    ->label(__('filament-panels::pages/auth/login.form.remember.label')),
            ])->statePath('data');
    }
}
