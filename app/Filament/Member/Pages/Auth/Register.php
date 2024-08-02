<?php

namespace App\Filament\Member\Pages\Auth;

use App\Helpers\Util;
use App\Models\Config;
use App\Models\Invitation;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;

class Register extends AuthRegister
{
  public function register(): ?RegistrationResponse
  {
    try {
      $this->rateLimit(2);
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
    if (!$invitation = $this->validateInvitationCode($data['uuid'] ?? null)) {
      return null;
    }

    $user = $this->handleRegistration($data);

    $this->form->model($user)->saveRelationships();
    /** @var Authenticatable $user */
    event(new Registered($user));

    /** @var Model $user */
    $this->sendEmailVerificationNotification($user);

    $user->invitation_id = $invitation->id;
    $invitation->is_redeemed = true;
    $invitation->save();
    $user->save();

    /** @var Authenticatable $user */
    Filament::auth()->login($user);

    session()->regenerate();
    $user->addComment('Se registro');

    return app(RegistrationResponse::class);
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
      ->rule(Password::default())
      ->same('passwordConfirmation')
      ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
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
