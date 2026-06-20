# Apéndice B — Comandos útiles para coordinar con el equipo técnico

El panel `/admin` cubre las operaciones rutinarias del administrador, pero algunas tareas excepcionales (regeneración del sitemap, reproceso de digests, consultas masivas a la bitácora) requieren ejecución de comandos `artisan` en el servidor. El administrador no ejecuta estos comandos directamente; los solicita al equipo técnico. Este apéndice consolida los comandos más útiles, su efecto y cuándo pedirlos, para que su comunicación con el equipo técnico sea precisa y rápida.

> **Importante.** Ninguno de los comandos de este apéndice debe ejecutarse sin autorización del responsable técnico del entorno. Solicítelos por el canal habitual, indique el motivo y guarde el resultado para futuras referencias.

## B.1 Información del sistema

### Estado de la cola

```bash
php artisan queue:work --once
```

Procesa **un único** job pendiente. Útil cuando se sospecha que la cola está atascada y se quiere verificar que el worker general no esté caído sin disparar un procesamiento masivo.

```bash
php artisan queue:listen
```

Inicia un worker en primer plano. Útil para observar en vivo qué jobs se procesan y detectar fallos. **No** se usa en producción; en producción la cola corre vía supervisor o equivalente.

### Estado del scheduler

```bash
php artisan schedule:list
```

Lista las tareas programadas y la próxima fecha de ejecución de cada una. Útil cuando se sospecha que un digest diario o semanal no se está despachando.

## B.2 Datos de demostración

### Seeders disponibles

```bash
php artisan db:seed --class=Spec009DemoSeeder
```

Genera datos de demostración consistentes con la especificación 009 (incluida la pareja organización suspendida + ofertas cerradas). Útil para reproducir flujos en entornos de prueba.

```bash
php artisan db:seed --class=GuidesDemoSeeder
```

Promueve cuentas seedeadas a credenciales reproducibles para el pipeline de capturas de documentación. Idempotente. Pensado para ejecutarse después de `Spec009DemoSeeder`.

> **Atención.** Los seeders **no** deben ejecutarse en producción salvo en escenarios de bootstrap explícito de un entorno nuevo. Confirme con el responsable técnico el entorno destino antes de pedir esta ejecución.

## B.3 Operaciones específicas del módulo

### Regenerar el sitemap público

```bash
php artisan app:generate-sitemap
```

Reconstruye el archivo `public/sitemap.xml` con todas las ofertas activas. La tarea se ejecuta automáticamente cuando se aprueba una nueva oferta, pero puede solicitarse manualmente si se sospecha que el sitemap quedó desincronizado.

### Reproceso de digests

Los digests diarios y semanales se despachan automáticamente. Si una ejecución programada se omitió (cron caído, mantenimiento) y se requiere reenviar, solicite al equipo técnico:

```bash
php artisan tinker
> App\Actions\Member\DispatchDailyDigestAction::run();
> App\Actions\Member\DispatchWeeklyDigestAction::run();
```

> **Importante.** El reproceso manual puede duplicar correos si se ejecuta dos veces o si se solapa con la ejecución automática. Coordine con el equipo técnico para asegurar que se trata de una sola ejecución segura.

### Validar integridad de organizaciones

```bash
php artisan tinker
> App\Models\Organization::query()->whereNotNull('suspended_at')->whereNull('suspended_by')->count();
```

Cuenta organizaciones con bandera de suspensión inconsistente (`suspended_at` presente pero `suspended_by` ausente). En condiciones normales, el resultado debe ser `0`. Un valor mayor que `0` indica datos inconsistentes y amerita investigación.

## B.4 Mantenimiento

### Limpiar la caché de Filament

```bash
php artisan filament:optimize-clear
```

Borra la caché de componentes de Filament. Útil tras cambios en la configuración de paneles, recursos o widgets. Suele ser una acción del equipo técnico tras una actualización del producto.

### Limpiar caché de configuración

```bash
php artisan config:clear
php artisan cache:clear
```

Limpian la caché de configuración y la caché de aplicación. Se usan tras cambios en archivos `.env` o tras detectar comportamientos anómalos atribuibles a configuración antigua.

## B.5 Auditoría

### Consultar eventos de bitácora

Las consultas tabulares contra `activity_log` no son comandos artisan; son consultas SQL ejecutadas por el equipo técnico. Las plantillas de consulta más útiles son:

```sql
-- Todos los eventos de suspensión en los últimos 30 días
SELECT created_at, subject_id, properties->>'$.suspension_reason' AS reason
FROM activity_log
WHERE event = 'organization-suspended'
  AND created_at >= NOW() - INTERVAL 30 DAY
ORDER BY created_at DESC;

-- Fallos de despacho de correo de un destinatario específico
SELECT created_at, event, properties->>'$.exception_class' AS exception
FROM activity_log
WHERE event LIKE 'mail-%-dispatch-failed'
  AND properties->>'$.recipient' = '<correo@example.com>'
ORDER BY created_at DESC;

-- Acciones ejecutadas por un usuario en una fecha concreta
SELECT created_at, event, description, subject_type, subject_id
FROM activity_log
WHERE causer_type = 'App\\Models\\User'
  AND causer_id = <user_id>
  AND created_at BETWEEN '<YYYY-MM-DD>' AND '<YYYY-MM-DD>'
ORDER BY created_at;
```

## B.6 Información para la solicitud

Cuando pida la ejecución de cualquier comando o consulta al equipo técnico, incluya:

| Campo | Ejemplo |
|---|---|
| Entorno destino | `producción` / `staging` / `local` |
| Comando exacto | `php artisan app:generate-sitemap` |
| Motivo | `Verificación de sincronización tras reporte de candidato.` |
| Resultado esperado | `Sitemap regenerado con timestamp posterior a la hora del reporte.` |
| Persona responsable de leer el resultado | (su nombre) |

Una solicitud bien formada acelera la respuesta y reduce ambigüedades en la coordinación.

## B.7 Referencias cruzadas

- **Capítulo 9** — descripción de los procesos automáticos asociados a alertas y digests.
- **Capítulo 10** — formato y contenido de la bitácora de auditoría.
- **Capítulo 11** — síntomas operativos donde alguno de estos comandos puede ser parte de la solución.
- *Guía de Implementación* — detalles operativos de despliegue, configuración del worker y del scheduler.
