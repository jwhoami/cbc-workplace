<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
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
