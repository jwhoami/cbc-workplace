# Capítulo 11 — Resolución de problemas

Este capítulo recopila los síntomas operativos más frecuentes que un administrador puede encontrar al utilizar el panel `/admin` y propone una primera vía de diagnóstico para cada uno. Está pensado como una referencia rápida: cada entrada describe un síntoma específico, las causas probables ordenadas de más a menos común y los pasos concretos para confirmar o descartar cada causa antes de escalar al equipo técnico.

> **Nota.** Cuando este capítulo recomienda "escalar al equipo técnico", incluya en su mensaje: la URL exacta donde ocurrió el problema, la hora aproximada, el nombre de usuario afectado y, si lo conoce, el ID del registro (organización, oferta, postulación). Estos cuatro datos reducen significativamente el tiempo de respuesta.

## 11.1 No puedo iniciar sesión

Síntomas:
- Tras pulsar **Iniciar sesión**, el formulario vuelve a aparecer con un mensaje genérico bajo el campo de usuario.
- Sus credenciales no son aceptadas.

Causas probables y verificaciones:

1. **Ingresó el correo en lugar del nombre de usuario.** El panel `/admin` autentica por `username`, no por correo. Pruebe con el `username` que le entregaron.
2. **La contraseña está caducada o fue restablecida.** Pida a otro administrador que la regenere desde la sección de usuarios (capítulo 7).
3. **Está intentando entrar al panel equivocado.** Las cuentas de `/member` no funcionan en `/admin` y viceversa. Verifique la URL.
4. **Múltiples intentos activaron el CAPTCHA.** Espere algunos minutos y vuelva a intentar.

Si las cuatro verificaciones se descartan, escale al equipo técnico con el username afectado.

## 11.2 El dashboard no muestra los widgets

Síntomas:
- Entró al panel correctamente, pero el dashboard aparece vacío o sin los cuatro widgets descritos en el capítulo 3.

Causas probables:

1. **Su rol no tiene permiso de administrador.** Los widgets validan `User->isAdmin()` antes de renderizar (capítulo 3, sección 3.1). Solicite revisión de su rol.
2. **El navegador conservó un estado antiguo.** Recargue con <kbd>Ctrl</kbd>+<kbd>F5</kbd> o equivalente para forzar recarga sin caché.

## 11.3 El widget de verificaciones pendientes muestra organizaciones que ya verifiqué

Síntoma:
- Acaba de verificar una organización y aparece todavía en el widget.

Causa probable:
- El widget no se actualiza automáticamente. Recargue la página manualmente; el widget refleja el estado en el momento de la carga.

## 11.4 La acción "Verificar" falla con un error

Síntomas:
- Pulsó **Verificar** en una organización y el sistema muestra un error o se queda colgado.

Causas probables:

1. **Servicio de correo caído.** La verificación envía un correo sincrónicamente ([`OrganizationVerification.php:52`](../../../app/Actions/Admin/OrganizationVerification.php)); si el SMTP falla, la operación aborta. Espere unos minutos y reintente; si persiste, escale al equipo técnico.
2. **La organización ya está verificada y otra pestaña reflejaba el estado antiguo.** Recargue la página y verifique el estado actual.

## 11.5 Suspendí una organización y no llegó el correo

Síntoma:
- Confirmó la suspensión; el badge cambió pero la organización afirma no haber recibido la notificación.

Causas probables:

1. **El worker de cola está caído.** El correo de suspensión se envía por cola ([`SuspendOrganization.php:87`](../../../app/Actions/Admin/SuspendOrganization.php)). Si el worker no está procesando, el correo permanece encolado. Escale al equipo técnico para verificar el estado del worker.
2. **Filtro de spam en el destinatario.** Pida al destinatario revisar carpeta de correo no deseado.
3. **La dirección de correo del miembro administrador es inválida.** Verifique el correo en el detalle de la organización; si parece inválido, contacte a la organización por otro canal.
4. **El despacho falló y quedó registrado en la bitácora.** Consulte la bitácora del evento `mail-suspension-dispatch-failed` para esa organización (capítulo 10, sección 10.6, patrón 1).

## 11.6 Reactivé una organización pero sus ofertas siguen cerradas

Síntoma:
- Tras reactivar la organización, las ofertas que estaban activas antes de la suspensión continúan en `CLOSED`.

Causa:
- Comportamiento intencional. La reactivación no reabre ofertas cerradas en cascada (capítulo 4, sección 4.5). La organización debe recrearlas desde su panel `/member` y pasar por el flujo de aprobación.

Esta restricción no es un error; es una decisión del producto para forzar una revisión actualizada del contenido tras una suspensión.

## 11.7 La acción "Decisión" no aparece en una oferta pendiente

Síntoma:
- Abrió la vista de detalle de una oferta y no ve el botón **Decisión** en la cabecera.

Causas probables:

1. **La oferta ya no está en estado `PENDING`.** La acción está restringida a `state = PENDING` ([`ViewJobListing.php:30`](../../../app/Filament/Admin/Resources/JobListingResource/Pages/ViewJobListing.php)). Si otro administrador la procesó antes, verifique el estado actual.
2. **Su rol no tiene permiso de aprobación.** Solicite revisión de su rol al super-administrador.

## 11.8 El correo de aprobación de oferta no llegó

Síntoma:
- Aprobó una oferta; cambió a `ACTIVE`, pero la organización no recibió la notificación.

Causa probable:
- Servicio de correo caído al momento de la aprobación. El correo de aprobación se envía sincrónicamente ([`JobListingApproval.php:49`](../../../app/Actions/Admin/JobListingApproval.php)), pero el resto del flujo (cambio de estado, dispatch del evento de alertas) ya está persistido. Escale para reenvío manual al equipo técnico.

## 11.9 Una oferta aprobada no disparó alertas instantáneas

Síntoma:
- Aprobó una oferta hace varios minutos y los candidatos con alerta instantánea no han recibido el correo.

Causas probables:

1. **Worker de cola caído.** Los correos de alertas instantáneas se encolan después del dispatch del evento; sin worker no salen. Escale.
2. **No hay candidatos con criterios coincidentes.** Es normal; la oferta puede no calzar con ninguna alerta activa.
3. **El dispatch del evento falló.** Consulte la bitácora con el equipo técnico.

## 11.10 No puedo eliminar una categoría

Síntoma:
- Al intentar eliminar una categoría, el sistema muestra un error o un modal de confirmación distinto al esperado.

Causa probable:
- La categoría tiene ofertas activas o históricas asociadas. La política de integridad impide la eliminación. Prefiera **renombrarla** a "Histórico — <Nombre>" en lugar de eliminarla (capítulo 6, sección 6.5).

## 11.11 No veo el menú "Usuarios" o "Roles" en el sidebar

Síntoma:
- Expandió **Sistema** pero no aparecen las opciones de usuarios o roles.

Causa:
- Su rol no tiene permiso sobre los recursos `User` y `Role`. Solicite al super-administrador revisión de su rol.

## 11.12 Un candidato reporta no recibir digests diarios

Síntoma:
- Un candidato afirma estar suscrito a una alerta diaria que no le llega.

Causas probables ordenadas:

1. **El cron del sistema no está ejecutando la tarea programada.** Escale al equipo técnico para revisar el scheduler.
2. **El destinatario tiene la alerta desactivada.** Pídale revisar el listado de alertas en su panel `/member` y confirmar que la alerta sigue activa.
3. **El servicio de correo está rechazando la dirección.** Consulte la bitácora con `event = 'mail-daily-dispatch-failed'` y `properties.recipient = <correo del candidato>`.

## 11.13 La página de un recurso queda en blanco tras pulsar una acción

Síntoma:
- Tras pulsar **Verificar**, **Suspender**, **Aprobar** u otra acción, la página se queda en blanco o muestra un error 500.

Causa:
- Excepción no controlada del lado del servidor. La acción puede haber persistido (revise el detalle del recurso tras recargar) o haber fallado completamente (sin efecto). Escale al equipo técnico inmediatamente: aporte hora exacta y URL.

## 11.14 La bitácora no muestra una acción que recuerdo haber ejecutado

Síntoma:
- Está investigando un cambio y no encuentra la entrada esperada en la sección de auditoría del recurso.

Causas probables:

1. **El cambio fue automático.** No todas las modificaciones generan entradas en la bitácora; algunas operaciones masivas o de mantenimiento se hacen sin trazabilidad de causer.
2. **Está buscando en el recurso equivocado.** La cascada de cierre de ofertas tras suspensión deja entradas en la organización, no en cada oferta individual.
3. **La entrada existe pero la vista del recurso no la presenta.** Solicite al equipo técnico una consulta directa en la tabla `activity_log` filtrando por `subject_id` (capítulo 10, sección 10.3.2).

## 11.15 ¿Cuándo escalar al equipo técnico?

Escale inmediatamente cuando:

- La operación produce error 500 o página en blanco.
- Los correos de cualquier flujo no llegan después de varios minutos sin causa evidente.
- Una acción crítica (suspensión, aprobación) queda en estado ambiguo (la página se cargó parcialmente).
- Sospecha de cuenta comprometida (intentos de inicio de sesión que usted no realizó, cambios que usted no aprobó).
- La bitácora muestra eventos `mail-*-dispatch-failed` recurrentes con la misma `exception_class`.

Antes de escalar, recopile: URL, hora, usuario afectado, ID de recurso, mensaje exacto del error si lo hay. Si el problema es reproducible, indique los pasos para reproducirlo.

Con este capítulo cierra el cuerpo principal de la guía. Los dos apéndices siguientes consolidan el glosario y los comandos artisan útiles cuando necesite coordinar con el equipo técnico.
