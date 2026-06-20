# Capítulo 4 — El patrón Actions

**Resumen ejecutivo.** CBC Workplace usa `lorisleiva/laravel-actions` (^2.7) como vehículo principal para la lógica de negocio. Cada operación significativa (suspender una organización, aprobar una oferta, despachar un digest) vive en una clase única bajo `app/Actions/`. Esto desplaza la complejidad fuera de controladores y resources, facilita el testing aislado y permite invocar la misma operación desde HTTP, CLI, queue o eventos sin duplicar código. Este capítulo explica los tres traits del paquete, los patrones de uso del repositorio y los pitfalls comunes al testear.

## 4.1 Por qué este patrón

La alternativa estándar (service classes inyectados por DI) requiere un constructor, un binding en el container y, típicamente, una capa de fachadas o aliases para invocación cómoda. `lorisleiva/laravel-actions` reemplaza ese boilerplate con un trait que aporta:

- Invocación estática `Action::run(...)`.
- Dispatch como job con `::dispatch(...)`.
- Registro como listener vía `::asListener()`.
- Soporte transparente para inyección por DI cuando el constructor lo necesita.

El equipo del producto adoptó este patrón porque las acciones de Bolsa de Trabajo son operaciones únicas con efectos colaterales bien delimitados —no entidades de larga vida que requieran composición—. El trade-off es **menor descubribilidad inicial** (los newcomers tienen que aprender el paquete), compensado por **mayor consistencia** entre las 40+ acciones del producto.

## 4.2 Los tres traits

El paquete provee tres traits del namespace `Lorisleiva\Actions\Concerns\`:

### 4.2.1 `AsAction`

El más usado. Permite invocación síncrona estática.

```php
use Lorisleiva\Actions\Concerns\AsAction;

class SuspendOrganization
{
    use AsAction;

    public function handle(Organization $org, ?string $reason = null): SuspendOrganizationResult
    {
        // ...
    }
}

// Uso:
$result = SuspendOrganization::run($organization, $reason);
```

> Patrón visible en [`app/Actions/Admin/SuspendOrganization.php:20-24`](../../../app/Actions/Admin/SuspendOrganization.php).

### 4.2.2 `AsJob`

Para acciones que también deben poder despacharse a la cola. El paquete genera un `JobDecorator` envoltorio.

```php
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsJob;

class BuildDigestForAlertAction
{
    use AsAction, AsJob;

    public function handle(JobAlert $alert, Collection $listings): void
    {
        // ...
    }
}

// Invocación síncrona:
BuildDigestForAlertAction::run($alert, $listings);

// Dispatch a la cola:
BuildDigestForAlertAction::dispatch($alert, $listings);
```

### 4.2.3 `AsListener`

Para suscribir la acción a un evento del sistema.

```php
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsListener;

class FanOutInstantAlerts
{
    use AsAction, AsListener;

    public function handle(JobListingApproved $event): void
    {
        // procesa alertas instantáneas
    }
}
```

Y se registra en `EventServiceProvider`:

```php
protected $listen = [
    JobListingApproved::class => [
        FanOutInstantAlerts::class,
    ],
];
```

## 4.3 Anatomía de una Action típica

Ejemplo concreto: [`app/Actions/Member/SubmitApplication.php:18-30`](../../../app/Actions/Member/SubmitApplication.php).

```php
<?php

namespace App\Actions\Member;

use Lorisleiva\Actions\Concerns\AsAction;

class SubmitApplication
{
    use AsAction;

    public function handle(Member $member, JobListing $listing, array $data = []): Application
    {
        $profile = $member->candidateProfile;
        if (! $profile) {
            throw new \Exception(__('models/application.notifications.no_profile'));
        }

        if ($listing->state !== JobListingState::ACTIVE
            || ($listing->application_deadline && $listing->application_deadline->isPast())
        ) {
            throw new \Exception(__('models/application.notifications.listing_inactive'));
        }

        // ... lógica de submission
    }
}
```

Convenciones observables:

1. **Namespace por contexto**: `App\Actions\Admin` para acciones del panel admin, `App\Actions\Member` para acciones del panel member; el directorio raíz `App\Actions` para acciones globales o programadas.
2. **Una clase por acción**: una sola responsabilidad por archivo.
3. **`handle()` como entry point**: el método público obligatorio.
4. **Validación de pre-condiciones temprano**: lanzar excepción si los invariantes no se cumplen.
5. **Cambios persistidos vía Eloquent**: no se construyen queries crudas.
6. **Eventos y correos al final**: tras persistir, dispatchar eventos y encolar correos.

## 4.4 Resultados tipados

Algunas acciones críticas devuelven un objeto resultado en lugar de un valor primitivo o void. Por ejemplo, [`SuspendOrganization`](../../../app/Actions/Admin/SuspendOrganization.php) retorna `SuspendOrganizationResult`:

```php
class SuspendOrganizationResult
{
    public static function suspended(int $offersDeactivated, int $notificationsEnqueued): self;
    public static function alreadySuspended(): self;
    // ...
}
```

Esto permite al caller distinguir entre "se suspendió y se cerraron N ofertas" y "ya estaba suspendida, no hicimos nada", sin recurrir a códigos de estado mágicos o excepciones para flujos esperados.

## 4.5 Transacciones

Cuando una acción modifica múltiples filas o tablas, debe envolver el bloque en `DB::transaction(...)` para garantizar atomicidad. Ejemplo de [`SuspendOrganization::handle():32-70`](../../../app/Actions/Admin/SuspendOrganization.php):

```php
$offersDeactivated = DB::transaction(function () use ($organization, $reason): int {
    $organization->suspended_at = now();
    $organization->suspended_by = Filament::auth()->user()?->name ?? 'Sistema';
    $organization->suspension_reason = $reason;
    $organization->save();

    $count = JobListing::query()
        ->where('organization_id', $organization->id)
        ->where('state', JobListingState::ACTIVE)
        ->update([
            'state' => JobListingState::CLOSED,
            'closed_at' => now(),
        ]);

    // ... comentarios + log
    return $count;
});
```

> **Importante.** Eventos y correos **deben dispatchar fuera** del bloque `DB::transaction`. Si se hacen dentro, una excepción posterior haría rollback de los cambios pero los correos ya estarían encolados o enviados, generando inconsistencia. El patrón habitual es: transacción → log de auditoría → eventos/correos.

## 4.6 Llamadas desde Filament Resources

Los Resources de Filament invocan acciones desde sus header actions o bulk actions. Patrón típico en una `ViewRecord` page:

```php
Actions\Action::make('approve-reject-job-listing')
    ->visible(fn (JobListing $record) => $record->state === JobListingState::PENDING)
    ->action(function (JobListing $record, array $data) {
        Util::run(fn () => JobListingApproval::run($record, $data));
        $this->redirect(JobListingResource::getUrl('view', ['record' => $record]));
    })
    ->form([
        Forms\Components\Radio::make('decision')->required(),
        Forms\Components\Textarea::make('approval_reason')
            ->requiredIf('decision', JobListingState::REJECTED->value),
    ]);
```

> Fuente: [`app/Filament/Admin/Resources/JobListingResource/Pages/ViewJobListing.php:27-49`](../../../app/Filament/Admin/Resources/JobListingResource/Pages/ViewJobListing.php).

El wrapper `Util::run(...)` envuelve la invocación con manejo uniforme de excepciones y notificaciones de Filament (toast de éxito o de error).

## 4.7 Testing de Actions

### 4.7.1 Test síncrono estándar

Para acciones con `AsAction`, los tests usan invocación directa:

```php
it('suspends an organization and closes its active offers', function () {
    $org = Organization::factory()->verified()->create();
    JobListing::factory()->for($org)->active()->create();

    $result = SuspendOrganization::run($org, reason: 'Test');

    expect($org->refresh()->is_suspended())->toBeTrue();
    expect($org->jobListings()->where('state', JobListingState::CLOSED)->count())->toBe(1);
    expect($result)->toBeInstanceOf(SuspendOrganizationResult::class);
});
```

### 4.7.2 Test de queue fake

Para acciones con `AsJob`, una trampa común: `Queue::fake()->assertPushed()` espera la clase del **`JobDecorator`** del paquete, **no** la clase de su Action.

```php
Queue::fake();
SomeAction::dispatch($model);

// ❌ INCORRECTO — falla:
Queue::assertPushed(SomeAction::class);

// ✅ CORRECTO — el paquete envuelve en un JobDecorator:
Queue::assertPushed(JobDecorator::class, function ($job) {
    return $job->decorated instanceof SomeAction;
});
```

> Documentado como feedback `feedback_laravel_actions_testing.md` en la memoria del proyecto. Si su test falla con "expected SomeAction to be pushed", revise primero esta convención.

### 4.7.3 Test de listener

Para acciones con `AsListener`, los tests pueden disparar el evento y aserción sobre los efectos colaterales:

```php
event(new JobListingApproved($listing));
expect(JobAlertDispatchLog::where('job_listing_id', $listing->id)->count())->toBeGreaterThan(0);
```

## 4.8 Catálogo de Actions del repositorio

### 4.8.1 `app/Actions/Admin/`

| Action | Trait(s) | Propósito |
|---|---|---|
| `SuspendOrganization` | AsAction | Suspende organización, cierra ofertas en cascada |
| `ReactivateOrganization` | AsAction | Libera suspensión (sin reabrir ofertas) |
| `OrganizationVerification` | AsAction | Verifica organización (`PENDING → VERIFIED`) |
| `JobListingApproval` | AsAction | Aprueba o rechaza oferta `PENDING` |
| `AnonymizeMemberApplications` | AsAction | Acción excepcional GDPR |
| `MembershipApproval` | AsAction | Aprueba membresía |
| `VentureApproval`, `VentureToggleActive` | AsAction | (módulo Emprendimientos) |

### 4.8.2 `app/Actions/Member/`

Más de 30 acciones; las más relevantes para Bolsa de Trabajo:

| Action | Trait(s) | Propósito |
|---|---|---|
| `RequestOrganizationVerification` | AsAction | Una org pide verificación |
| `RequestJobListingApproval` | AsAction | Una org envía oferta a aprobación |
| `CloseJobListing` | AsAction | Org cierra manualmente su oferta |
| `SubmitApplication` | AsAction | Candidato postula a una oferta |
| `UpdateApplicationStatus` | AsAction | Org cambia el estado de una postulación |
| `AddApplicationNote`, `UpdateApplicationNote`, `DeleteApplicationNote` | AsAction | Notas sobre postulaciones |
| `CreateJobAlertAction`, `ToggleJobAlertAction`, `DeleteJobAlertAction` | AsAction | Gestión de alertas |
| `DisableJobAlertByTokenAction` | AsAction | Desuscripción vía link firmado |

### 4.8.3 `app/Actions/` (raíz)

| Action | Propósito |
|---|---|
| `ExpireJobListings` | Comando programado para llevar ofertas vencidas a `EXPIRED` |
| `Sponsor` | (legado, módulo Emprendimientos) |

## 4.9 Cuándo NO usar una Action

El patrón no aplica para:

- **Operaciones que son CRUD trivial sin lógica**: estos los cubren los Resources de Filament directamente.
- **Helpers puros y stateless**: viven en `app/Helpers/` o como métodos estáticos.
- **Lógica que depende de DI compleja con varios servicios externos**: aunque `lorisleiva/laravel-actions` soporta DI por constructor, en estos casos el patrón service-class clásico puede leer mejor.

## 4.10 Resumen

| Pregunta | Respuesta |
|---|---|
| ¿Dónde vive la lógica de negocio? | `app/Actions/{Admin,Member,/}*.php` |
| ¿Cómo invoco una Action sincrónicamente? | `Action::run($a, $b)` |
| ¿Cómo despacho a la cola? | `Action::dispatch($a, $b)` con trait `AsJob` |
| ¿Cómo testeo dispatch a la cola? | `Queue::assertPushed(JobDecorator::class, fn ($j) => $j->decorated instanceof Action)` |
| ¿Dónde van eventos y correos? | Fuera del `DB::transaction`, después de persistir |

El próximo capítulo (5) describe los modelos de Bolsa de Trabajo y sus relaciones.
