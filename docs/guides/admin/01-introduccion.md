# Capítulo 1 — Introducción

Esta guía es la referencia operativa del **panel de administración** de CBC Workplace, accesible en la ruta `/admin`. Está destinada a las personas con rol de super-administrador o moderador, responsables de aprobar organizaciones, supervisar publicaciones de empleo, mantener la taxonomía del sitio y auditar la actividad del sistema. El documento parte del supuesto de que usted ya recibió credenciales de acceso y que la instalación está operativa; las tareas de despliegue, configuración del entorno y resolución de problemas a nivel de servidor se cubren en la *Guía de Implementación*.

## 1.1 Para quién es esta guía

Esta guía está dirigida a:

- **Super-administradores** que aprueban organizaciones, gestionan usuarios del propio panel y toman decisiones de suspensión y reactivación.
- **Moderadores** que revisan ofertas de empleo pendientes y supervisan las postulaciones recibidas.
- **Personal de soporte** que utiliza el panel para consultar el estado de una cuenta, una publicación o una postulación a pedido del usuario final.

Se asume familiaridad con interfaces administrativas tipo Filament (sidebar, tabla con filtros, formularios modales) y una comprensión básica del flujo del producto. No se requieren conocimientos de PHP, Laravel ni de la base de datos: cuando un procedimiento exige intervención del equipo de desarrollo, el texto lo indica explícitamente.

## 1.2 Alcance del panel administrativo

El panel `/admin` agrupa la navegación en cuatro grupos, definidos en [`app/Providers/Filament/AdminPanelProvider.php:47-56`](../../../app/Providers/Filament/AdminPanelProvider.php):

| Grupo | Recursos que incluye | Cubierto en esta guía |
|---|---|---|
| **Sistema** | Usuarios del panel, roles, configuraciones | Capítulo 7 |
| **Administración** | Categorías globales, textos editoriales | Capítulo 6 (categorías) |
| **Bolsa de Trabajo** | Organizaciones, empleos, postulaciones, perfiles de candidato, categorías de empleo | Capítulos 4, 5, 6, 8 |
| **Emprendimientos** | Miembros (members), ventures | Fuera de alcance v1.0 |

Esta guía v1.0 cubre exhaustivamente todo lo relacionado con el módulo **Bolsa de Trabajo** (especificaciones 002–009) y los recursos de Sistema necesarios para administrar el propio equipo del panel. El módulo de **Emprendimientos** queda fuera de alcance en esta versión; se cubrirá en una edición posterior.

![Dashboard administrativo con los cuatro widgets de Bolsa de Trabajo.](../screenshots/admin/admin-dashboard-full.png)

*Figura 1.1 — Vista general del panel `/admin` recién iniciado, mostrando el grupo de navegación **Bolsa de Trabajo** activo y los cuatro widgets introducidos por la especificación 009.*

## 1.3 Cómo leer esta guía

Cada capítulo se estructura de la siguiente forma:

- **Resumen inicial** de tres a cinco líneas con el objetivo del capítulo.
- **Índice local** que enumera las secciones.
- **Secciones numeradas** con explicaciones conceptuales antes de los procedimientos prácticos.
- **Procedimientos paso-a-paso** numerados (1., 2., 3.) cuando hay una acción concreta que ejecutar.
- **Bloques de aviso** que destacan información que no debe pasarse por alto:

> **Nota.** Aclaración o contexto adicional. Puede saltarse sin perder el hilo principal.

> **Atención.** Comportamiento contraintuitivo o efecto secundario que conviene tener presente.

> **Importante.** Acción destructiva, irreversible o con impacto operativo serio. Léala antes de continuar.

> **Buena práctica.** Recomendación derivada de la experiencia operativa, no obligatoria pero altamente sugerida.

Al final de cada procedimiento encontrará un párrafo breve titulado *Qué esperar después*, que describe el efecto observable del cambio (correo enviado, badge actualizado, registro en bitácora de auditoría, etc.).

## 1.4 Convenciones tipográficas

| Convención | Significado |
|---|---|
| **Negrita** | Nombres de botones, ítems de menú y etiquetas visibles en la interfaz |
| *Cursiva* | Término introducido por primera vez o título de otra guía |
| `código` | Identificadores, rutas, valores literales |
| <kbd>Tecla</kbd> | Pulsación de teclado |

Las referencias internas al código del producto siguen el formato `archivo:línea`. Por ejemplo, `app/Models/Organization.php:41` indica el archivo `app/Models/Organization.php` y la línea 41. Estas referencias se incluyen para que el equipo técnico pueda auditar cada afirmación, y no es necesario consultarlas durante la operación normal.

## 1.5 Glosario operativo

Los siguientes términos aparecen recurrentemente. Las definiciones canónicas viven en el blueprint del proyecto ([`docs/guides/00-blueprint.md`](../00-blueprint.md), sección 3) y deben respetarse sin reinterpretación.

| Término | Significado en esta guía |
|---|---|
| **Panel Admin** | La interfaz Filament expuesta en `/admin`, autenticada con el guard `admin`. |
| **Panel Member** | La interfaz Filament expuesta en `/member`, utilizada por organizaciones y candidatos. |
| **Portal público** | Las páginas accesibles sin sesión (`/bolsa-de-trabajo` y sus subrutas). |
| **Organización** | Entidad que publica empleos. Tiene un estado de verificación (PENDING o VERIFIED) y banderas independientes de suspensión. |
| **Suspensión** | Bandera operativa que congela una organización; bloquea publicaciones nuevas y cierra automáticamente las ofertas activas. No es un estado de verificación. |
| **Reactivación** | Acción inversa a la suspensión; preserva el estado de verificación previo y no reabre las ofertas que fueron cerradas en cascada. |
| **Empleo / Oferta** | Publicación de un puesto vacante. Atraviesa los estados DRAFT, PENDING, ACTIVE, REJECTED, CLOSED y EXPIRED. |
| **Postulación / Aplicación** | Candidatura enviada por un candidato a una oferta. Estados: RECEIVED, IN_REVIEW, INTERVIEW, REJECTED, ACCEPTED. |
| **Alerta de empleo** | Suscripción del candidato a criterios de búsqueda; produce *digests* periódicos por correo. |
| **Digest** | Correo electrónico agrupador que entrega las ofertas coincidentes a un candidato con alerta activa. |
| **Widget** | Panel resumen visible en el dashboard de `/admin`. La versión 1.0 incluye cuatro widgets: estadísticas globales, aprobaciones pendientes de ofertas, verificaciones pendientes de organizaciones y postulaciones recientes. |
| **Activity Log** | Bitácora persistente de cambios sensibles (verificación, suspensión, aprobación, envíos de correo). Implementada con `spatie/laravel-activitylog`. |

Términos no listados aquí, pero presentes en la interfaz o el código, se irán introduciendo en el capítulo correspondiente y se consolidarán en el **Apéndice A — Glosario**.

## 1.6 Lo que esta guía no cubre

Para evitar ambigüedades, se enumeran explícitamente los temas que pertenecen a otras guías:

- **Despliegue, configuración de servidor, secretos, colas de procesos, cron** → *Guía de Implementación*, capítulos 2 y 8.
- **Uso del portal público y del panel Member desde el punto de vista del candidato o de la organización publicadora** → *Guía del Usuario*.
- **Cambios en el código fuente, migraciones, extender el modelo** → *Guía de Implementación*, capítulos 4 y 9.
- **Branding y reemplazo del logotipo** → *Guía de Implementación*, capítulo 11 (changelog y configuración).

Si una tarea no aparece en esta guía y tampoco está en las anteriores, contacte al equipo de mantenimiento del producto: probablemente se trate de una funcionalidad no liberada o de un caso límite que debe documentarse explícitamente.

## 1.7 Versión, fecha y atribución

Esta guía corresponde a la versión **v1.0 — Mayo 2026** del producto CBC Workplace, alineada con las especificaciones 002 a 009 ya integradas en la rama `main` del repositorio `hooperits/cbc-workplace`. Autor responsable: **Juan Carlos Hooper**. El proyecto se desarrolla para **Crossroads Bible Church**.

Cualquier discrepancia entre lo descrito en este documento y el comportamiento observado del producto debe reportarse al equipo de mantenimiento; las referencias `archivo:línea` que acompañan a cada afirmación permiten validar rápidamente si la divergencia se debe a un cambio reciente del código o a una imprecisión de la documentación.
