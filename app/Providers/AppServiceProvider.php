<?php

namespace App\Providers;

use App\Http\Responses\EmailVerificationResponse;
use Filament\Http\Responses\Auth\Contracts\EmailVerificationResponse as EmailVerificationResponseContract;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use Illuminate\Support\ServiceProvider;
use Filament\Forms;
use App\Helpers\AppMacros;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->bind(LoginResponseContract::class, LoginResponse::class);
    $this->app->bind(EmailVerificationResponseContract::class, EmailVerificationResponse::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {

    AppMacros::actionHasAuthorization();
    AppMacros::actionRequiresAuthorization();

    Forms\Components\DateTimePicker::configureUsing(function (Forms\Components\DateTimePicker $field) {
      $field
        ->native(false)
        ->displayFormat(config('appx.dateTimeFormat.display.dateTime'))
        ->format(config('appx.dateTimeFormat.database.dateTime'));
    });

    Forms\Components\DatePicker::configureUsing(function (Forms\Components\DatePicker $field) {
      $field
        ->native(false)
        ->displayFormat(config('appx.dateTimeFormat.display.date'))
        ->format(config('appx.dateTimeFormat.database.date'));
    });

    Forms\Components\Toggle::configureUsing(function (Forms\Components\Toggle $field) {
      $field
        ->inline(false);
    });
  }
}
