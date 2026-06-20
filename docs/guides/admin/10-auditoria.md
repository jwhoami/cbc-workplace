# Capítulo 10 — Auditoría

CBC Workplace registra los eventos sensibles del módulo Bolsa de Trabajo en una **bitácora de auditoría** persistente, implementada con el paquete `spatie/laravel-activitylog`. La bitácora es el primer lugar al que el administrador debe acudir cuando necesite reconstruir qué pasó, quién lo hizo y cuándo. Este capítulo describe los eventos que se registran, dónde se almacenan, cómo consultarlos y qué información acompaña a cada uno.

## 10.1 Modelo de la bitácora

Cada entrada de la bitácora vive en la tabla `activity_log` y contiene:

- **`log_name`** o **`event`**: el identificador del tipo de evento (por ejemplo, `organization-suspended`).
- **`subject`**: el modelo afectado (Organization, JobListing, Application, etc.) referenciado por `subject_type` + `subject_id`.
- **`causer`**: el usuario que originó el evento (cuando aplica), referenciado por `causer_type` + `causer_id`.
- **`description`**: texto descriptivo legible.
- **`properties`**: bloque JSON con metadatos del evento (IP, conteos, identificadores secundarios, razones).
- **`created_at`**: marca temporal del evento.

Los modelos que producen entradas en la bitácora usan el trait `Spatie\Activitylog\Traits\LogsActivity`. Por ejemplo, [`app/Models/Organization.php`](../../../app/Models/Organization.php) declara la configuración del log en los métodos `getActivitylogOptions()` y `tapActivity()`.

## 10.2 Eventos registrados

La siguiente tabla enumera los eventos relevantes para el administrador, agrupados por flujo:

### 10.2.1 Organizaciones

| Evento | Origen | Properties típicas |
|---|---|---|
| `organization-verified` | [`OrganizationVerification.php:39-41`](../../../app/Actions/Admin/OrganizationVerification.php) | (mínimo: subject, causer) |
| `organization-suspended` | [`SuspendOrganization.php:72-80`](../../../app/Actions/Admin/SuspendOrganization.php) | `ip`, `organization_id`, `offers_deactivated`, `suspension_reason` |
| `organization-reactivated` | [`ReactivateOrganization.php:31-37`](../../../app/Actions/Admin/ReactivateOrganization.php) | `ip`, `organization_id` |
| `mail-suspension-dispatch-enqueued` | [`SuspendOrganization.php:89-93`](../../../app/Actions/Admin/SuspendOrganization.php) | `recipient` |
| `mail-suspension-dispatch-failed` | [`SuspendOrganization.php:96-103`](../../../app/Actions/Admin/SuspendOrganization.php) | `recipient`, `exception_class` |

![Figura 10.1 — Resultado tabular de una consulta directa contra `activity_log` filtrada por una organización suspendida (consulta solicitada al equipo técnico).](../screenshots/admin/admin-activitylog-org-suspended.png)

### 10.2.2 Empleos

| Evento | Origen | Properties típicas |
|---|---|---|
| `job-listing.approve` | [`JobListingApproval.php:45-47`](../../../app/Actions/Admin/JobListingApproval.php) | (mínimo: subject, causer) |
| `job-listing.reject` | [`JobListingApproval.php:66-68`](../../../app/Actions/Admin/JobListingApproval.php) | (mínimo; la razón vive en el comentario) |

### 10.2.3 Alertas (envío de digests)

| Evento | Significado |
|---|---|
| `mail-instant-dispatch-enqueued` | Correo de alerta instantánea encolado con éxito |
| `mail-instant-dispatch-failed` | Fallo al encolar correo instantáneo |
| `mail-daily-dispatch-enqueued` | Digest diario encolado |
| `mail-daily-dispatch-failed` | Fallo al encolar digest diario |
| `mail-weekly-dispatch-enqueued` | Digest semanal encolado |
| `mail-weekly-dispatch-failed` | Fallo al encolar digest semanal |

## 10.3 Cómo consultar la bitácora

La versión 1.0 del panel `/admin` **no** expone aún un visor dedicado de la bitácora. La consulta se realiza por uno de estos caminos:

### 10.3.1 Por modelo afectado

Cuando necesite revisar el historial de una organización, oferta o postulación específica, abra su vista de detalle en el panel. La sección de comentarios internos al final de la vista incluye también los registros de auditoría asociados, organizados cronológicamente. Esta es la vía más rápida cuando ya sabe qué entidad investiga.

### 10.3.2 Vía equipo técnico

Para investigaciones más amplias —por ejemplo, "¿qué eventos de suspensión ocurrieron en la última semana?", "¿qué administrador aprobó la mayoría de las ofertas en marzo?"—, solicite al equipo técnico una consulta directa contra la tabla `activity_log`. El equipo conoce los patrones de consulta habituales y puede entregarle el resultado en formato tabular o CSV.

> **Nota.** Un visor administrativo dedicado de la bitácora está en el backlog del producto. Hasta su entrega, la vía descrita aquí cubre las necesidades operativas habituales.

## 10.4 Retención

La bitácora no se purga automáticamente: las entradas se conservan indefinidamente para soportar investigaciones a largo plazo. Si en el futuro se establece una política de retención (por requisitos regulatorios, por ejemplo), se documentará en la *Guía de Implementación*. Hasta entonces, asuma que cualquier evento registrado es consultable.

## 10.5 Información sensible en properties

Los bloques `properties` de algunos eventos contienen información que requiere manejo cuidadoso:

- **`ip`**: dirección IP del operador al momento del evento. No se trata de un dato del candidato o de la organización, sino del propio administrador.
- **`recipient`** (en eventos de correo): el correo electrónico del destinatario. En entornos donde rija una política estricta de protección de datos, esto puede ser información personal sujeta a tratamiento.
- **`suspension_reason`**: texto libre que usted ingresó al suspender. Puede contener información organizacional sensible. Sea preciso pero no descriptivo en exceso.

> **Buena práctica.** Asuma que cualquier persona del equipo técnico que tenga acceso a la base de datos podrá leer la bitácora. Cuando ingrese una razón de suspensión o cualquier dato similar, escriba pensando en que ese texto será leído por terceros en el futuro.

## 10.6 Diagnóstico desde la bitácora

La bitácora es el insumo principal para diagnosticar incidentes operativos. A continuación, tres patrones de uso frecuentes:

### Patrón 1: una organización no recibió el correo de suspensión

1. Identifique la entrada `organization-suspended` correspondiente.
2. Busque entradas siguientes con `mail-suspension-dispatch-enqueued` o `mail-suspension-dispatch-failed` referidas a la misma organización.
3. Si hay `failed`, examine `exception_class` para diagnóstico.
4. Si hay `enqueued` pero el correo no llegó, el problema está aguas abajo del encolado (worker, servicio SMTP). Escale al equipo técnico.

### Patrón 2: auditar quién aprobó una oferta concreta

1. Abra la vista de detalle de la oferta.
2. Localice la entrada `job-listing.approve` en la sección de auditoría.
3. El campo `causer` muestra el administrador. El campo `created_at` muestra el momento.

### Patrón 3: revisar todas las suspensiones recientes

1. Solicite al equipo técnico una consulta `SELECT * FROM activity_log WHERE event = 'organization-suspended' ORDER BY created_at DESC LIMIT 50;` (o equivalente).
2. Revise `properties` de cada entrada para entender el contexto operativo.

## 10.7 Resumen

| Pregunta | Respuesta |
|---|---|
| ¿Dónde se registran los eventos? | Tabla `activity_log` vía `spatie/laravel-activitylog`. |
| ¿Cómo consulto el historial de una organización? | Vista de detalle de la organización, sección de auditoría y comentarios. |
| ¿Hay un visor administrativo global de la bitácora? | No en v1.0; consulta vía equipo técnico para análisis amplios. |
| ¿Las entradas se purgan? | No automáticamente. |

El capítulo siguiente (11) cierra esta guía con un compendio de síntomas operativos comunes y sus respuestas inmediatas.
