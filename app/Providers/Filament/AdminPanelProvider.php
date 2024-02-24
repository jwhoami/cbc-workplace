<?php

namespace App\Providers\Filament;

use Filament\Navigation\MenuItem;
use App\Filament\Admin\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->id('admin')
      ->path('admin')
      ->authGuard('admin')
      ->login(Login::class)
      ->colors([
        'primary' => Color::Amber,
        'gray' => Color::Gray
      ])
      ->topNavigation()
      ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
      ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
      ->widgets([
        Widgets\AccountWidget::class,
        Widgets\FilamentInfoWidget::class,
      ])
      ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        SubstituteBindings::class,
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
      ])
      ->authMiddleware([
        Authenticate::class,
      ])
      ->renderHook(
        PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        fn (): string => Blade::render('filament.components.admin-menu', [
          'items' => $this->getAdminMenuItems()
        ])
      );
  }

  protected function getAdminMenuItems(): array
  {
    $array = collect(config('filament.adminMenu.items', []))
      ->map(function ($item, $key) {
        return MenuItem::make($key)
          ->label($item['label'] ?? 'no label')
          ->url($item['url'] ?? '')
          ->icon($item['icon'] ?? '');
      })
      ->toArray();
    return $array;
  }
}
