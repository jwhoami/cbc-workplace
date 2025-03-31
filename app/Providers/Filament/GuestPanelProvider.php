<?php

namespace App\Providers\Filament;

use App\Filament\Guest\Resources\VentureResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class GuestPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->id('guest')
      ->path('')
      ->darkMode(false)
      ->colors([
        'primary' => Color::Amber,
        'gray' => Color::Gray,
      ])
      ->brandLogo(fn() => view('filament.logo'))
      ->topNavigation()
      ->discoverResources(in: app_path('Filament/Guest/Resources'), for: 'App\\Filament\\Guest\\Resources')
      ->discoverPages(in: app_path('Filament/Guest/Pages'), for: 'App\\Filament\\Guest\\Pages')
      ->pages([
        VentureResource\Pages\ListVentures::class,
        //Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Guest/Widgets'), for: 'App\\Filament\\Guest\\Widgets')
      ->widgets([
        Widgets\FilamentInfoWidget::class,
      ])
      ->breadcrumbs(false)
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
      ->authMiddleware([])
      ->renderHook(
        PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        function (): string {
          return "VISITANTE";
        }
      )
      //      ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn () => view('filament.components.guest-menu'))
      ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
        return $builder->items([
          NavigationItem::make(__('Portal'))
            ->icon('heroicon-o-home')
            ->isActiveWhen(fn(): bool => request()->routeIs('filament.guest.pages..'))
            ->url(function () {
              return url(route('filament.member.auth.login'));
            }),
          NavigationItem::make(__('Mi Cuenta'))
            ->url(url(route('filament.member.pages.dashboard')))
            ->icon('heroicon-o-arrow-down-circle')
            ->sort(3),
        ]);
      });
  }
}
