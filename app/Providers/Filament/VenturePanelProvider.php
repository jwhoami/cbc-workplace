<?php

namespace App\Providers\Filament;

use App\Filament\Venture\Resources\VentureResource;
use Filament\Facades\Filament;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
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

class VenturePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('/app')
            ->default()
            ->darkMode(false)
            ->colors([
                'primary' => Color::Cyan,
                'gray' => Color::Slate,
                'warning' => Color::Amber,
            ])
            ->brandLogo(fn () => view('filament.logo'))
            ->topNavigation()
            ->discoverResources(in: app_path('Filament/Venture/Resources'), for: 'App\\Filament\\Venture\\Resources')
            ->discoverPages(in: app_path('Filament/Venture/Pages'), for: 'App\\Filament\\Venture\\Pages')
            ->pages([
                VentureResource\Pages\ListVentures::class,
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Venture/Widgets'), for: 'App\\Filament\\Venture\\Widgets')
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
          // ->renderHook(
          //   PanelsRenderHook::GLOBAL_SEARCH_AFTER,
          //   function (): string {
          //     return "VISITANTE";
          //   }
          // )
          //      ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn () => view('filament.components.venture-menu'))
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->items([
                    NavigationItem::make(__('Inicio'))
                        ->icon('heroicon-o-home')
                        ->url(function () {
                            return url('/');
                        }),
                    NavigationItem::make(__('Mis Favoritos'))
                        ->icon('heroicon-o-heart')
                        ->visible(fn () => auth()->guard('member')->user())
                        ->url(function () {
                            return url()->route('filament.member.resources.favorites.index');
                        }),
                    // NavigationItem::make(__('Registrar'))
                    //   ->icon('heroicon-o-user-plus')
                    //   ->url(function () {
                    //     return url(route('filament.member.auth.register'));
                    //   }),
                    // NavigationItem::make(__('Mi Cuenta'))
                    //   ->url(url(route('filament.member.pages.dashboard')))
                    //   ->icon('heroicon-o-arrow-down-circle')
                    //   ->sort(3),
                ]);
            });
    }
}
