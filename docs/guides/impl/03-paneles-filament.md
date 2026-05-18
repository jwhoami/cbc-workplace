# Capítulo 3 — Paneles Filament

**Resumen ejecutivo.** Los tres `PanelProvider` viven en [`app/Providers/Filament/`](../../../app/Providers/Filament/) y comparten el mismo patrón estructural: `id()`, `path()`, `authGuard()`, descubrimiento automático de resources/pages/widgets, stack de middleware y render hooks específicos. Este capítulo enumera las diferencias relevantes entre los tres paneles, las decisiones de configuración tomadas y los puntos de extensión que un desarrollador toca con mayor frecuencia.

## 3.1 Comparativa rápida

| Propiedad | `/admin` | `/member` | `/app` (Venture) |
|---|---|---|---|
| Archivo | `AdminPanelProvider.php` | `MemberPanelProvider.php` | `VenturePanelProvider.php` |
| `id()` | `'admin'` | `'member'` | `'app'` |
| `path()` | `admin` | `member` | `/app` |
| `authGuard()` | `'admin'` | `'member'` | (vacío) |
| `default()` | no | no | **sí** |
| `login()` | `Admin\Pages\Auth\Login` | `Member\Pages\Auth\Login` | (sin login) |
| `registration()` | no | sí (`Register::class`) | no |
| `passwordReset()` | no | sí | no |
| `emailVerification()` | no | sí | no |
| `colors primary` | `Color::Amber` | `Color::Amber` | `Color::Amber` |
| `topNavigation()` | sí | sí | sí |
| `navigationGroups()` | 4 grupos explícitos | (vacío) | (vacío) |
| Plugin CAPTCHA | sí | sí (en Login) | n/a |
| Resource discovery | `app/Filament/Admin/Resources` | `app/Filament/Member/Resources` | `app/Filament/Venture/Resources` |

## 3.2 Panel admin

Verificable en [`app/Providers/Filament/AdminPanelProvider.php:27-93`](../../../app/Providers/Filament/AdminPanelProvider.php).

```php
return $panel
    ->id('admin')
    ->path('admin')
    ->authGuard('admin')
    ->login(Login::class)
    ->colors(['primary' => Color::Amber, 'gray' => Color::Gray])
    ->brandLogo(fn () => view('filament.logo'))
    ->topNavigation()
    ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
    ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
    ->pages([Pages\Dashboard::class])
    ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
    ->widgets([])
    ->navigationGroups([
        NavigationGroup::make()->label('Sistema'),
        NavigationGroup::make()->label('Administración'),
        NavigationGroup::make()->label(__('navigation.bolsa-de-trabajo')),
        NavigationGroup::make()->label('Emprendimientos'),
    ])
    // ... middleware ...
    ->renderHook(
        PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        fn (): string => 'ADMIN - '.Filament::auth()->user()->role->name
    )
    ->profile(EditProfile::class)
    ->plugin(\MarcoGermani87\FilamentCaptcha\FilamentCaptcha::make());
```

Puntos relevantes:

- **`authGuard('admin')`**: el guard `admin` se configura en `config/auth.php` apuntando al modelo `App\Models\User`.
- **Cuatro grupos de navegación** declarados explícitamente. Cada `Resource` retorna el grupo al que pertenece vía `getNavigationGroup()`.
- **Render hook `GLOBAL_SEARCH_AFTER`**: inyecta texto identificando el rol activo, sirve de recordatorio visual de contexto.
- **Plugin CAPTCHA**: globalmente registrado; el form de Login lo añade condicionalmente al schema (sección 3.5).
- El widget `[]` de `widgets()` está vacío porque los cuatro widgets de spec 009 se descubren automáticamente vía `discoverWidgets()`.

## 3.3 Panel member

Verificable en [`app/Providers/Filament/MemberPanelProvider.php:37-152`](../../../app/Providers/Filament/MemberPanelProvider.php). Es el panel con más configuración por ser el de mayor uso por parte de usuarios finales.

```php
return $panel
    ->id('member')
    ->path('member')
    ->authGuard('member')
    ->login(Login::class)
    ->registration(Register::class)
    ->authPasswordBroker('members')
    ->passwordReset()
    ->emailVerification()
    ->profile(EditProfile::class)
    // ...
    ->renderHook(
        PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        function (): string {
            return (auth()->guard('member')->user()->membership_state === MembershipState::APPROVED)
                ? 'AFILIADO'
                : 'REGISTRADO';
        }
    )
    ->navigation(function (NavigationBuilder $builder): NavigationBuilder { /* ... */ });
```

Particularidades del panel member:

- **Auto-registro habilitado**: `registration()`, `passwordReset()`, `emailVerification()` están todos activos. Las cuentas se crean por el propio usuario, no por el admin.
- **`authPasswordBroker('members')`**: usa un broker separado (configurado en `config/auth.php`) para que el reseteo de password no use el broker default de Laravel.
- **Custom email verification**: el método `boot()` en `MemberPanelProvider.php:37-46` sobreescribe el mensaje de verificación para usar español personalizado.
- **Banner de organización suspendida**: render hook `CONTENT_START` en `MemberPanelProvider.php:48-62` renderiza un banner cuando la organización del miembro autenticado está suspendida (decisión spec 009 §R4).
- **Navegación construida manualmente**: a diferencia del admin, el member usa `->navigation(NavigationBuilder)` para tener control fino sobre los items visibles según `membership_state`.

### 3.3.1 Banner de suspensión

```php
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
```

> Fuente: [`MemberPanelProvider.php:48-62`](../../../app/Providers/Filament/MemberPanelProvider.php).

El banner usa la view `resources/views/filament/member/banners/organization-suspended.blade.php` y se renderiza en la parte superior de cada página del panel cuando aplica. El criterio es el método `Organization::is_suspended()`.

## 3.4 Panel venture (/app)

Verificable en [`app/Providers/Filament/VenturePanelProvider.php:23-92`](../../../app/Providers/Filament/VenturePanelProvider.php).

```php
return $panel
    ->id('app')
    ->path('/app')
    ->default()
    ->darkMode(false)
    ->colors(['primary' => Color::Amber, 'gray' => Color::Gray])
    // ...
    ->authMiddleware([])  // <- panel público
    ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
        return $builder->items([
            NavigationItem::make(__('Inicio'))->icon('heroicon-o-home')->url(fn () => url('/')),
            NavigationItem::make(__('Mis Favoritos'))
                ->visible(fn () => auth()->guard('member')->user())
                ->url(fn () => url()->route('filament.member.resources.favorites.index')),
        ]);
    });
```

Particularidades:

- **`default()`**: este panel es el default; URLs sin prefijo apuntan a él (vía `RouteServiceProvider`).
- **`authMiddleware([])`**: sin middleware de autenticación. El panel es público, accesible sin sesión.
- **Sin login propio**: si un visitante quiere autenticarse, debe ir a `/member/login`.
- **Navegación condicional**: el ítem "Mis Favoritos" solo aparece si hay sesión activa en el guard `member`.

Este panel queda fuera de alcance de la documentación v1.0 del módulo Bolsa de Trabajo; se incluye aquí únicamente por completitud arquitectónica.

## 3.5 Login form del panel admin

Verificable en [`app/Filament/Admin/Pages/Auth/Login.php`](../../../app/Filament/Admin/Pages/Auth/Login.php).

```php
public function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('username')
            ->label(__('login.fields.username.label'))
            ->required()
            ->autocomplete('username'),
        TextInput::make('password')
            ->password()
            ->revealable()
            ->required(),
        ...(app()->environment(['testing', 'local']) ? [] : [
            CaptchaField::make('captcha'),
        ]),
        Checkbox::make('remember'),
    ])->statePath('data');
}
```

> Fuente: [`app/Filament/Admin/Pages/Auth/Login.php:18-39`](../../../app/Filament/Admin/Pages/Auth/Login.php).

Decisiones de diseño:

- **`username` en lugar de `email`**: el panel admin autentica por nombre de usuario (no por correo). El método `getCredentialsFromFormData()` pasa `['username' => ..., 'password' => ...]` al guard, no `email`.
- **CAPTCHA condicional**: omitido en `local` y `testing` para que el desarrollo iterativo y los tests automatizados no requieran resolverlo. Activo en cualquier otro entorno (incluyendo `staging` y `production`).
- **Mensaje genérico de fallo** (`throwFailureValidationException`): no distingue entre usuario inexistente y password incorrecta para no facilitar enumeración de cuentas.

El Login del panel **member** usa `email` (no username) y mantiene el patrón estándar de Filament.

## 3.6 Middleware compartido

Los tres paneles aplican el mismo stack base para integrarse con Filament:

```php
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
```

`AuthenticateSession::class` invalida la sesión cuando el usuario es eliminado o cambia su password en otra sesión. `DispatchServingFilamentEvent` permite que listeners reaccionen al rendering del panel.

## 3.7 Descubrimiento automático vs. registro explícito

Filament soporta dos modos para Resources/Pages/Widgets:

- **`discover*`**: escanea un directorio y registra todo lo que encuentre.
- **`->pages([Class1::class, ...])`** y **`->widgets([...])`**: registro manual explícito.

Los tres paneles usan principalmente `discover*` con un fallback de registro explícito para casos específicos:

```php
->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
->pages([Pages\Dashboard::class])  // <- registro explícito del Dashboard built-in de Filament
```

El `Dashboard` de Filament no vive en `app/Filament/Admin/Pages/`, sino en el paquete; por eso requiere registro explícito.

## 3.8 Plugin CAPTCHA

```php
->plugin(\MarcoGermani87\FilamentCaptcha\FilamentCaptcha::make());
```

> Fuente: [`AdminPanelProvider.php:76`](../../../app/Providers/Filament/AdminPanelProvider.php).

El plugin registra el componente `CaptchaField` que el Login usa condicionalmente. Configuración del provider del CAPTCHA vive en el paquete; ninguna variable de entorno adicional es necesaria para desarrollo.

## 3.9 EditProfile

Cada panel registra su propia página de edición de perfil:

```php
->profile(EditProfile::class)
```

Para admin, la clase está en [`app/Filament/Admin/Pages/EditProfile.php`](../../../app/Filament/Admin/Pages/EditProfile.php); para member, en `app/Filament/Member/Pages/EditProfile.php`. Las dos extienden la página base de Filament añadiendo campos custom del proyecto.

## 3.10 Cómo añadir un panel nuevo

Si en el futuro se necesita un cuarto panel (por ejemplo, partner integrations):

1. Crear `app/Providers/Filament/PartnerPanelProvider.php` extendiendo `PanelProvider`.
2. Registrar en `bootstrap/providers.php` (o `config/app.php` según versión).
3. Crear directorio `app/Filament/Partner/{Resources,Pages,Widgets}`.
4. Configurar el guard `partner` en `config/auth.php` apuntando al modelo apropiado.
5. Definir el `path()` y `authGuard()` evitando colisiones con los tres paneles existentes.

> **Atención.** El panel `default()` solo puede ser uno. Actualmente lo es Venture (`VenturePanelProvider.php:30`). Cambiar el panel default afecta la resolución de URLs sin prefijo en Filament.

## 3.11 Resumen

| Pregunta | Respuesta |
|---|---|
| ¿Cuántos paneles tiene el producto? | Tres: admin, member, venture. |
| ¿Cuál es el panel default? | Venture (`/app`). |
| ¿Qué panel requiere autenticación? | Admin y member. Venture es público. |
| ¿Dónde está el banner de suspensión? | Render hook `CONTENT_START` en `MemberPanelProvider.php:48-62`. |
| ¿Cómo se autentica el admin? | Por `username`, no email; CAPTCHA en producción. |
| ¿Cómo se autentica el member? | Por email; flujo completo de registro + reset + email verification. |

El próximo capítulo (4) explica el patrón de `Actions` que sostiene la lógica de negocio del producto.
