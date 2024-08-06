<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\Login;
use App\Filament\Admin\Resources\MemberResource;
use App\Filament\Admin\Pages\EditProfile;
use App\Filament\Admin\Resources\TextResource;
use App\Filament\Member\Resources\VentureResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
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
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
      ->topNavigation()
      ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
      ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
      ->widgets([
//        Widgets\AccountWidget::class,
//        Widgets\FilamentInfoWidget::class,
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
        fn (): string => 'ADMIN-PANEL'
      )
      ->renderHook(
        PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        fn (): string => Blade::render('filament.components.admin-menu', [
          'items' => $this->getAdminMenuItems(),
        ])
      )
      ->profile(EditProfile::class)
      ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
        return $builder->items([
          NavigationItem::make(__('Inicio'))
          ->icon('heroicon-o-home')
          ->isActiveWhen(fn (): bool => request()->routeIs('filament.guest.pages..'))
          ->url('/'),
          NavigationItem::make('Dashboard')
          ->icon('heroicon-o-squares-2x2')
          ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
          ->url(fn (): string => Pages\Dashboard::getUrl()),
          ...TextResource::getNavigationItems(),
          ...MemberResource::getNavigationItems(),
          ...VentureResource::getNavigationItems(),
        ]);
      });
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
