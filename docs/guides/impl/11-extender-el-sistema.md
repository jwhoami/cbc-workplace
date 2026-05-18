# Capítulo 11 — Extender el sistema

**Resumen ejecutivo.** Añadir nuevas funcionalidades a CBC Workplace sigue patrones consistentes derivados de los capítulos anteriores. Este capítulo agrupa recetas paso-a-paso para las extensiones más comunes: un widget en el dashboard admin, una policy nueva, una Action de negocio, una categoría de empleo, un comando programado, y una columna en un modelo existente. Cada receta incluye los archivos a tocar, los tests obligatorios y los pitfalls habituales.

## 11.1 Añadir un widget al dashboard admin

Caso típico: surge la necesidad de un nuevo resumen visible al abrir `/admin`.

### Pasos

1. **Crear la clase** bajo [`app/Filament/Admin/Widgets/`](../../../app/Filament/Admin/Widgets/). Si es de tipo "estadísticas" (varias métricas en cards):
   ```bash
   sail artisan make:filament-widget MyStatsWidget --type=stats --resource=
   ```
   El descubrimiento automático (`discoverWidgets` en `AdminPanelProvider.php:45`) la registrará sin más configuración.

2. **Implementar `canView()` y `getStats()`** siguiendo el patrón de [`JobBoardStatsOverview`](../../../app/Filament/Admin/Widgets/JobBoardStatsOverview.php):
   ```php
   public static function canView(): bool
   {
       $user = Filament::auth()->user();
       return $user instanceof User && $user->isAdmin();
   }

   protected function getStats(): array
   {
       return [
           Stat::make('Métrica', (string) $this->computeValue())
               ->description('Aclaración'),
       ];
   }
   ```

3. **Definir `$sort`** para posicionarlo en el dashboard:
   ```php
   protected static ?int $sort = 5;  // después de los 4 widgets de spec 009
   ```

4. **Decidir si tiene polling**: `protected static ?string $pollingInterval = null;` para refrescar solo en recarga; `'30s'` para refrescar cada 30 segundos. Considere costo de DB.

5. **Localizar los strings** en `resources/lang/<locale>/widgets/admin/<archivo>.php`. Use `__('widgets/admin/...')` en el código.

6. **Test del widget**:
   ```php
   it('renders for admin users and aggregates the metric correctly', function () {
       $admin = User::factory()->admin()->create();
       MyMetric::factory()->count(3)->create();

       Livewire::actingAs($admin, 'admin')->test(MyStatsWidget::class)
           ->assertSeeText('3');
   });
   ```

### Pitfalls

- **Olvidar `canView()`**: el widget se renderiza para usuarios no admin. Siempre validar.
- **Query N+1 en `getStats()`**: agrupar todas las métricas en una o pocas consultas. El widget se renderiza cada page load del dashboard.

## 11.2 Añadir una Action de negocio

Caso típico: un nuevo flujo (p.ej. "Aprobar masivamente"), que no encaja como CRUD trivial.

### Pasos

1. **Decidir namespace**: `Admin/`, `Member/`, `Public/`, `Alerts/`, o raíz según contexto.

2. **Crear la clase** con `AsAction` y `AsJob` si debe poder ir a la cola:
   ```php
   namespace App\Actions\Admin;

   use Lorisleiva\Actions\Concerns\AsAction;

   class BulkApproveJobListings
   {
       use AsAction;

       public function handle(array $jobListingIds, User $approver): int
       {
           return DB::transaction(function () use ($jobListingIds, $approver) {
               // ... lógica
               return $count;
           });
       }
   }
   ```

3. **Si dispatcha correos o eventos**, hacerlos **fuera** del `DB::transaction` (capítulo 4 sección 4.5).

4. **Registrar entradas en activitylog** según el patrón:
   ```php
   Util::getActivityLog('bulk-approve-job-listings')
       ->causedBy($approver)
       ->withProperties(['count' => $count])
       ->log('Bulk approval');
   ```

5. **Invocar desde el Resource correspondiente** (típicamente como `BulkAction` en la tabla):
   ```php
   ->bulkActions([
       BulkAction::make('approve-all')
           ->action(function (Collection $records) {
               BulkApproveJobListings::run($records->pluck('id')->all(), auth()->user());
           }),
   ])
   ```

6. **Tests**:
   - Test happy path verificando estado final.
   - Test de excepción cuando ningún record cumple precondición.
   - Si `AsJob`, test de queue dispatch con el patrón `JobDecorator` (capítulo 4 sección 4.7.2).

### Pitfalls

- **Side effects dentro de `DB::transaction`**: si un correo se encola dentro y luego la transacción hace rollback, el correo ya se envió. Patrón siempre: transaction → eventos/correos.
- **No usar `Util::run` desde Filament actions**: pierde el manejo uniforme de errores con toast notifications.

## 11.3 Añadir una Policy o un método de Policy

Caso típico: una operación nueva que el sistema autoriza por rol.

### Pasos

1. **Si el modelo no tiene Policy**: `sail artisan make:policy MyModelPolicy --model=MyModel`. La convención de naming la auto-registra.

2. **Heredar de `BasePolicy`** para reutilizar el `before()` hook y los métodos genéricos:
   ```php
   class MyModelPolicy extends BasePolicy
   {
       public static $name = 'MyModel';

       public function customAction(Model $user, ?MyModel $record = null): bool
       {
           // lógica
       }
   }
   ```

3. **Si el modelo se gestiona desde el panel `/member` y la organización puede estar suspendida**:
   ```php
   public function customAction(Model $user, ?MyModel $record = null): bool
   {
       if ($user instanceof Member && Util::isPanelActive('member') && $record) {
           if ((new OrganizationPolicy)->organizationFrozenForMember($user, $record->organization)) {
               return false;
           }
           // ... reglas custom
       }
       return false;
   }
   ```

4. **Tests** con `Filament::setCurrentPanel(...)` para cada rama (capítulo 6 sección 6.9).

### Pitfalls

Las cuatro trampas documentadas en el capítulo 6 sección 6.10. Repetir aquí brevemente:

- Olvidar `setCurrentPanel` en tests.
- Confundir `User` con `Member`.
- Olvidar la verificación de suspensión.
- Asumir que `before()` cubre todo (solo cubre admin con `isAdmin()=true`).

## 11.4 Añadir una categoría de empleo

Caso típico: catálogo nuevo solicitado por el equipo de operación.

Si la categoría se añade desde la interfaz, el admin la crea desde **Bolsa de Trabajo → Categorías** (capítulo 6 de la *Guía de Admin*).

Si necesita seed programático (para tests o setup automatizado):

```php
Category::create([
    'name' => 'Pastoral',
    'slug' => 'pastoral',
    'icon' => 'heroicon-o-megaphone',
    'scope' => 'JobListing',
]);
```

> **Importante.** El campo `scope` discrimina categorías de empleo de las que usan otros módulos. Olvidarlo crea categorías huérfanas que no aparecen ni en el listado admin ni en el portal público.

## 11.5 Añadir un comando programado

Caso típico: una tarea cron nueva.

### Pasos

1. **Crear el comando**:
   ```bash
   sail artisan make:command MyTask
   ```

2. **Implementar `handle()`** delegando a una Action:
   ```php
   class MyTask extends Command
   {
       protected $signature = 'mytask:run';
       protected $description = 'Run my scheduled task';

       public function handle(): int
       {
           MyTaskAction::run();
           return Command::SUCCESS;
       }
   }
   ```

3. **Programarlo en `app/Console/Kernel.php`**:
   ```php
   $schedule->command('mytask:run')
       ->dailyAt('02:00')
       ->timezone(config('app.timezone'))
       ->withoutOverlapping()
       ->onOneServer();
   ```

4. **Test del comando**:
   ```php
   it('runs the scheduled task successfully', function () {
       $this->artisan('mytask:run')->assertSuccessful();
       // verificar efectos colaterales
   });
   ```

### Pitfalls

- **Olvidar `withoutOverlapping`**: si la ejecución previa demora, la siguiente arranca encima y duplica side effects.
- **Olvidar `onOneServer`**: en producción multi-nodo, el comando corre N veces.
- **No setear `timezone`**: usar la zona configurada en `config('app.timezone')` para que la hora sea predecible.

## 11.6 Añadir una columna a un modelo existente

Capítulo 5 sección 5.13 lo describe en detalle. Resumen:

1. `make:migration` + `Schema::table(... function ($table) { $table->...->nullable(); })`.
2. `migrate`.
3. `$fillable` y `$casts` del modelo si aplica.
4. `LogOptions::logOnly(...)` si debe auditarse.
5. Actualizar resources, policies, factories, tests.

> **Importante.** En producción, prefiera columnas **nullable**. Una columna `NOT NULL` requiere backfill antes de aplicar, lo que complica el deploy. Si necesita `NOT NULL` con default, escriba la migración con `->default(...)` para evitar el backfill manual.

## 11.7 Añadir un Resource Filament

Caso típico: exponer un modelo existente como nuevo CRUD en el panel admin.

### Pasos

1. **Crear el Resource**:
   ```bash
   sail artisan make:filament-resource MyModel --panel=admin --generate
   ```

2. **Asignar el grupo de navegación** declarando `getNavigationGroup()`:
   ```php
   public static function getNavigationGroup(): ?string
   {
       return __('navigation.bolsa-de-trabajo');
   }
   ```

3. **Definir el formulario y la tabla** en los métodos `form()` y `table()` del Resource.

4. **Definir páginas hijas** (`getPages()` con `index`, `view`, `create`, `edit`).

5. **Añadir header/bulk actions** que invoquen Actions del módulo (capítulo 11.2).

6. **Crear la Policy** del modelo si no existe (capítulo 11.3).

7. **Tests Feature** con `Livewire::test(...)` sobre las páginas.

### Pitfalls

- **Olvidar la Policy**: el resource carga pero las acciones fallan con `403 Unauthorized`.
- **Asumir que `discoverResources` cubre todo**: si el archivo no está en la raíz del directorio escaneado, no lo descubre.

## 11.8 Añadir un evento

Caso típico: un nuevo punto de extensión para que listeners desconocidos hoy puedan engancharse mañana.

### Pasos

1. **Crear el evento**:
   ```php
   namespace App\Events;

   class MyDomainEvent
   {
       use Dispatchable, SerializesModels;
       public function __construct(public Model $subject) {}
   }
   ```

2. **Dispatchar desde la Action correspondiente**, al final del método tras todas las mutaciones y dentro del bloque de éxito:
   ```php
   public function handle(...): ...
   {
       DB::transaction(function () { /* mutaciones */ });
       // dispatch fuera del bloque de transacción:
       MyDomainEvent::dispatch($subject);
   }
   ```

3. **Listeners**: pueden registrarse vía `EventServiceProvider::$listen` o usando el trait `AsListener` (capítulo 4 sección 4.2.3).

4. **Test del evento**:
   ```php
   Event::fake([MyDomainEvent::class]);
   SomeAction::run(...);
   Event::assertDispatched(MyDomainEvent::class);
   ```

## 11.9 Añadir un Mailable

Caso típico: nuevo correo transaccional.

### Pasos

1. `sail artisan make:mail MyMailable`.
2. Implementar `envelope()`, `content()` y `attachments()`.
3. Crear la view en `resources/views/mail/`.
4. Encolar desde la Action: `Mail::to($recipient)->queue(new MyMailable($context))`.
5. Test con `Mail::fake()` + `Mail::assertQueued(MyMailable::class, function ($mail) use (...) { ... })`.

### Pitfalls

- **Enviar con `send()` en lugar de `queue()`**: bloquea el request hasta que el SMTP responda. Reserve `send()` para flujos donde el correo sea parte del éxito de la operación (verificación de organización, por ejemplo); use `queue()` para todo lo demás.
- **No registrar en activitylog**: para flujos críticos (alertas, suspensiones), registre `mail-<tipo>-dispatch-enqueued` y `mail-<tipo>-dispatch-failed` con el `recipient` en `properties`.

## 11.10 Patrones de naming

| Tipo | Patrón |
|---|---|
| Action | `VerbNoun` (`SuspendOrganization`, `SubmitApplication`) |
| Event | `NounPastParticiple` (`JobListingApproved`) |
| Listener | `Verb<Event>` (`EvaluateInstantJobAlerts`) |
| Policy | `ModelPolicy` (`OrganizationPolicy`) |
| Resource | `ModelResource` (`OrganizationResource`) |
| Mailable | `<Recipient>\<Subject>` (`Member\JobListingApproved`, `Organization\Suspended`) |
| Migración | `verb_noun_to_table` (`add_suspension_columns_to_organizations`) |

## 11.11 Checklist al extender

- [ ] La nueva funcionalidad sigue los patrones del módulo (Action + Policy + Resource).
- [ ] Tests Feature + Unit con cobertura suficiente del happy path y los caminos de error.
- [ ] Si la funcionalidad afecta a miembros con organización: la lógica de **suspensión-frozen** está incluida.
- [ ] Eventos/correos van fuera de transacciones.
- [ ] Strings localizados (`__('...')`), no hardcodeados.
- [ ] file:line de cada decisión documentada en la PR.
- [ ] `composer audit` limpio si añadió dependencias.
- [ ] Migración aditiva (sin destructivos) cuando sea posible.

## 11.12 Resumen

| Extensión | Patrón principal |
|---|---|
| Widget admin | Clase bajo `Filament/Admin/Widgets/`, `canView()`, `getStats()`, `$sort` |
| Action de negocio | Clase bajo `Actions/<Context>/` con `AsAction` + DB::transaction |
| Policy | Hereda de `BasePolicy`, `$name` correcto, considere suspensión |
| Categoría | UI o seed con `scope='JobListing'` |
| Comando programado | `make:command` + entry en `Kernel::schedule()` |
| Columna en modelo | Migración nullable + casts + LogOptions + tests |
| Resource Filament | `make:filament-resource` + Policy + grupo + header actions |
| Evento | Clase en `Events/` + dispatch en Action + listener con `AsListener` |
| Mailable | `make:mail` + `queue()` no `send()` + log dispatch |

El próximo capítulo (12) cierra la guía con el changelog del producto y las relaciones entre las especificaciones liberadas.
