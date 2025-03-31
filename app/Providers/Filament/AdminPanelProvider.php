<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\Login;
use App\Filament\Admin\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;

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
        'gray' => Color::Gray,
      ])
      ->brandLogo(fn() => view('filament.logo'))
      ->topNavigation()
      ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
      ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
      ->widgets([])
      ->navigationGroups([
        NavigationGroup::make()
          ->label('Sistema'),
        NavigationGroup::make()
          ->label('Administración'),
        NavigationGroup::make()
          ->label('Emprendimientos'),
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
        fn(): string => 'ADMIN - ' . Filament::auth()->user()->role->name
      )
      ->profile(EditProfile::class)
      ->plugin(\MarcoGermani87\FilamentCaptcha\FilamentCaptcha::make());
    //      ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
    //        return $builder->items([
    //          NavigationItem::make(__('Inicio'))
    //          ->icon('heroicon-o-home')
    //          ->isActiveWhen(fn (): bool => request()->routeIs('filament.guest.pages..'))
    //          ->url('/'),
    //          NavigationItem::make('Dashboard')
    //          ->icon('heroicon-o-squares-2x2')
    //          ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
    //          ->url(fn (): string => Pages\Dashboard::getUrl()),
    //          ...TextResource::getNavigationItems(),
    //          ...CategoryResource::getNavigationItems(),
    //          ...MemberResource::getNavigationItems(),
    //          ...VentureResource::getNavigationItems(),
    //        ]);
    //      });
  }
}
