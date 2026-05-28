<?php

namespace App\Providers\Filament;

use App\Enums\MembershipState;
use App\Models\Organization;
use App\Filament\Member\Pages\Auth\Login;
use App\Filament\Member\Pages\Auth\Register;
use App\Filament\Member\Pages\EditProfile;
use App\Filament\Member\Resources\VentureResource;
use App\Filament\Member\Resources\CandidateProfileResource;
use App\Filament\Member\Resources\OrganizationResource;
use App\Filament\Member\Resources\JobListingResource;
use App\Filament\Member\Resources\ApplicationResource;
use App\Filament\Member\Resources\JobAlertResource;
use Filament\Facades\Filament;
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
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MemberPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->greeting('Estimado(a) '.$notifiable->name)
                ->subject('Verifique su dirección de correo electrónico')
                ->line('Por favor haga clic en el botón abajo para verificar su dirección de correo electrónico.')
                ->action('Verifique Email', $url)
                ->salutation('Gracias');
        });

        FilamentView::registerRenderHook(
            PanelsRenderHook::CONTENT_START,
            function (): string {
                $member = auth('member')->user();
                $organization = $member?->organization;

                if (! $organization || ! $organization->is_suspended()) {
                    return '';
                }

                return view('filament.member.banners.organization-suspended', [
                    'organization' => $organization,
                ])->render();
            },
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('member')
            ->path('member')
            ->authGuard('member')
            ->darkMode(false)
            ->login(Login::class)
            ->registration(Register::class)
            ->authPasswordBroker('members')
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class)
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Gray,
            ])
            ->brandLogo(fn () => view('filament.logo'))
            ->topNavigation()
            ->discoverResources(in: app_path('Filament/Member/Resources'), for: 'App\\Filament\\Member\\Resources')
            ->discoverPages(in: app_path('Filament/Member/Pages'), for: 'App\\Filament\\Member\\Pages')
            ->pages([
                // Pages\Dashboard::class,
                // VentureResource\Pages\ListVentures::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Member/Widgets'), for: 'App\\Filament\\Member\\Widgets')
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
            ->authMiddleware([Authenticate::class])
            ->userMenuItems([
                MenuItem::make()
                    ->label(__('Contacto'))
                    ->url(fn (): string => url()->route('member-contact'))
                    ->icon('heroicon-o-cog-6-tooth'),
                MenuItem::make()
                    ->label(__('Manual de Usuario'))
                    ->url(fn (): string => url()->route('member-contact'))
                    ->icon('heroicon-o-document')
                    ->url(function () {
                        return Storage::disk('public')->url('manual_de_usuario.pdf');
                    })
                    ->openUrlInNewTab(),
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                function (): string {
                    return (auth()->guard('member')->user()->membership_state === MembershipState::APPROVED) ? 'AFILIADO' : 'REGISTRADO';
                }
            )
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $items = [
                    NavigationItem::make(__('Inicio'))
                        ->icon('heroicon-o-home')
                        ->url('/')
                        ->openUrlInNewTab(),
                    NavigationItem::make(__('Bolsa de Trabajo'))
                        ->icon('heroicon-o-briefcase')
                        ->url('/bolsa-de-trabajo')
                        ->openUrlInNewTab(),
                ];
                if (auth()->guard('member')->user()) {
                    $hasOrganization = Organization::where('member_id', auth('member')->id())->exists();

                    array_push($items, ...[
                        NavigationItem::make('Dashboard')
                            ->icon('heroicon-o-squares-2x2')
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.member.pages.dashboard'))
                          // ->visible(fn (): bool => Filament::auth()->user()->membership_state === MembershipState::APPROVED)
                            ->url(fn (): string => Pages\Dashboard::getUrl()),
                        NavigationItem::make(__('Favoritos'))
                            ->icon('heroicon-o-heart')
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.member.resources.favorites.index'))
                            ->url(url(route('filament.member.resources.favorites.index'))),
                        ...VentureResource::getNavigationItems(),
                        ...CandidateProfileResource::getNavigationItems(),
                        ...OrganizationResource::getNavigationItems(),
                        ...($hasOrganization ? JobListingResource::getNavigationItems() : []),
                        ...ApplicationResource::getNavigationItems(),
                        ...JobAlertResource::getNavigationItems(),
                    ]);
                }
                $items = $builder->items($items);

                return $items;
            });
    }
}
