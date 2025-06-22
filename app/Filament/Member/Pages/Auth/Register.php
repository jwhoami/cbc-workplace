<?php

namespace App\Filament\Member\Pages\Auth;

use App\Helpers\Util;
use App\Http\Responses\MemberRegistrationResponse;
use App\Models\Config;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\Text;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use MarcoGermani87\FilamentCaptcha\Forms\Components\CaptchaField;

class Register extends AuthRegister
{

  //   protected $rules = [
  //     'password' => [
  //         'required',
  //         'string',
  //         Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
  //         'confirmed'
  //     ],
  // ];

  public function mount(): void
  {
    parent::mount();
    if (Config::make()->getp('invitationCodeRequiredForRegistration', false)) {
      $code = request()->input('i', null);
      if (! $code && Config::make()->getp('invitationCodeRequiredForRegistration', true)) {
        redirect(route('member-register-with-invitation-code'));
      }
    }
    FilamentView::registerRenderHook(
      PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
      fn(): View => view('filament.member.register-page-footer-links'),
    );
  }

  public function register(): ?MemberRegistrationResponse
  {
    try {
      $this->rateLimit(Config::make()->getp("rateLimiter.register"));
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

      return null;
    }

    $data = $this->form->getState();
    $invitationCodeRequiredForRegistration = Config::make()->getp('invitationCodeRequiredForRegistration', false);


    if ($invitationCodeRequiredForRegistration) {
      if (!$invitation = $this->validateInvitationCode($data['uuid'] ?? null)) {
        return null;
      }
    }

    if (! $data['tos'] ?? null) {
      Util::filamentNotification(__("Favor acepte los términos y condiciones"), "warning");
      return null;
    }

    $user = $this->handleRegistration($data);

    $this->form->model($user)->saveRelationships();
    /** @var Authenticatable $user */
    event(new Registered($user));

    /** @var Model $user */
    $this->sendEmailVerificationNotification($user);

    $latestTos = Text::latestText('terminos-y-condiciones')->first();

    $user->invitation_id = $invitation->id ?? null;
    $user->tos = $latestTos?->id;

    if ($invitationCodeRequiredForRegistration) {
      $invitation->is_redeemed = true;
      $invitation->save();
    }

    $user->save();

    /** @var Authenticatable $user */
    Filament::auth()->login($user);

    session()->regenerate();
    $user->addComment('Se registro');

    return app(MemberRegistrationResponse::class);
  }

  protected function getForms(): array
  {
    return [
      'form' => $this->form(
        $this->makeForm()
          ->schema([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
            ViewField::make('tos')
              ->view('filament.components.form-tos'),
            CaptchaField::make('captcha')
              ->helperText(__("Acepta los caracteres sin importar mayúscula o minúscula")),
            Hidden::make('uuid')
              ->default(request()->i),
          ])
          ->statePath('data'),
      ),
    ];
  }

  protected function getPasswordFormComponent(): Forms\Components\Component
  {
    return TextInput::make('password')
      ->label(__('filament-panels::pages/auth/register.form.password.label'))
      ->password()
      ->revealable()
      ->required()
      ->rule(Password::min(8)->mixedCase()->numbers())
      ->same('passwordConfirmation')
      ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
      ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
  }

  protected function validateInvitationCode($uuid = null): ?Invitation
  {
    $invitationCodeRequiredForRegistration = Config::make()->getp('invitationCodeRequiredForRegistration', true);
    if (!$invitationCodeRequiredForRegistration) {
      return null;
    }

    if (!$uuid) {
      Util::filamentNotification('Registro es por invitación', 'danger');
      return null;
    }
    $invitation = Invitation::where('uuid', $uuid)->first();
    if (!$invitation) {
      Util::filamentNotification('Invitación invalida', 'danger');
      return null;
    }
    if ($invitation->redeemed) {
      Util::filamentNotification('Invitación invalida', 'danger');
      return null;
    }
    if (now()->greaterThan($invitation->expires_at)) {
      Util::filamentNotification('Invitación vencida', 'danger');
      return null;
    }
    //    if (!$invitation->sponsor->can_sponsor) {
    //      Util::filamentNotification('Invitación invalida', 'danger');
    //    }
    return $invitation;
  }

  protected function afterValidate(): void
  {
    $this->invitation->delete();
  }
}
