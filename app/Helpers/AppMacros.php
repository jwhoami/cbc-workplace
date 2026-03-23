<?php

namespace App\Helpers;

use App\Helpers\Util;
use Filament\Actions\Action;
use Filament\Actions\MountableAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AppMacros
{
  public static function actionHasAuthorization()
  {
    MountableAction::macro('hasAuthorization', function ($key) {
      /** @var Action $this */
      return $this->visible(function () use ($key) {
        $user = Filament::auth()->user();
        return $user->hasPermission($key);
      });
    });
  }

  public static function actionRequiresAuthorization()
  {
    MountableAction::macro('requiresAuthorization', function ($key, $record = null) {
      /** @var Action $this */
      return $this->before(function ($action) use ($key, $record) {
        $allowed = false;
        $message = '';
        if ($record) {
          $response = Gate::inspect($key, $record);
          $allowed = $response->allowed();
          $message = $response->message();
        } else {
          $allowed = Filament::auth()->user()->hasPermission($key);
          $message = config('appx.messages.OPERATION-UNAUTHORIZED');
        }
        if (! $allowed) {
          Util::filamentNotification($message, 'warning');
          $action->cancel();
        }
      });
    });
  }

  public static function requiresPasswordConfirmation()
  {
    MountableAction::macro('requiresPasswordConfirmation', function (bool | \Closure $condition = true) {
      /** @var Action $this */
      return $this
        ->form(fn (MountableAction $action) => ! $action->evaluate($condition) ? null : [
          TextInput::make('password')
            ->label(__('models/user.fields.password'))
            ->password()
            ->revealable(),
        ])
        ->modalAlignment(fn (MountableAction $action): ?Alignment => $action->evaluate($condition) ? Alignment::Center : null)
        ->modalFooterActionsAlignment(fn (MountableAction $action): ?Alignment => $action->evaluate($condition) ? Alignment::Center : null)
        ->modalWidth(fn (MountableAction $action): ?MaxWidth => $action->evaluate($condition) ? MaxWidth::Medium : null)
        ->modalDescription(fn (MountableAction $action): ?string => $action->evaluate($condition) ? __('common.actions.confirm-password.description') : null)
        ->before(function (MountableAction $action, array $data) {
          $confirmed = Hash::check($data['password'], auth()->user()->password);

          if (! $confirmed) {
            Util::filamentNotification(__('common.actions.confirm-password.exception'), 'danger');
            $action->cancel();
          }
        });
    });
  }
}
