# Spec 008: Notificaciones y Alertas de Empleo

## Contexto

Para mantener a los candidatos enganchados con la plataforma y asegurar que no pierdan oportunidades relevantes, la Bolsa de Trabajo de Lazos de Fe necesita un sistema de alertas de empleo. Los miembros pueden suscribirse a alertas configurables que les notifican por email cuando se publican nuevas ofertas que coinciden con sus intereses, ya sea por categoria de empleo, ciudad, o ambos. Este modulo complementa las notificaciones transaccionales de specs anteriores (postulaciones, aprobaciones) con notificaciones proactivas de descubrimiento.

## Que debe hacer

### Suscripcion a alertas
- Un miembro registrado puede crear una o mas alertas de empleo.
- Cada alerta se configura con: categoria de empleo de interes (opcional — si no se selecciona, aplica a todas las categorias), ciudad de interes (opcional — si no se especifica, aplica a cualquier ciudad), y frecuencia de notificacion (diaria, semanal, o instantanea).
- El miembro puede activar o desactivar cada alerta sin eliminarla.
- El miembro puede editar o eliminar sus alertas.
- La gestion de alertas se realiza desde el panel de miembro.

### Envio de alertas
- Las alertas diarias se envian una vez al dia con un resumen de las nuevas ofertas publicadas en las ultimas 24 horas que coinciden con los criterios del miembro.
- Las alertas semanales se envian una vez por semana con un resumen de las nuevas ofertas de la semana.
- Las alertas instantaneas se envian tan pronto como se publica una nueva oferta que coincide con los criterios.
- El email de alerta incluye un listado de las ofertas que coinciden con enlaces directos a cada una.
- Si no hay ofertas nuevas que coincidan, no se envia email (para evitar correos vacios).
- El envio de alertas se procesa de forma asincrona mediante colas de trabajo.

### Comandos programados
- Un comando programado se ejecuta diariamente para procesar las alertas con frecuencia diaria.
- Un comando programado se ejecuta semanalmente para procesar las alertas semanales.
- Las alertas instantaneas se disparan como evento al momento de la aprobacion de una oferta.

## Por que es necesario

Las alertas de empleo son un mecanismo clave para la retencion de usuarios y la efectividad de la plataforma. Sin alertas, los candidatos tendrian que visitar la plataforma manualmente para descubrir nuevas ofertas, lo cual reduce significativamente la probabilidad de que encuentren oportunidades relevantes. Las alertas convierten a la plataforma de un sitio pasivo a un servicio proactivo que trabaja para el candidato.

## Dependencias

- **Spec 002** (Categorias de Empleo): las alertas pueden filtrarse por categoria.
- **Spec 005** (Ofertas de Empleo): las alertas se disparan cuando se publican nuevas ofertas.

## Decisiones de integracion

- Las alertas pertenecen a un miembro (`Member`), reutilizando el modelo de autenticacion existente.
- La relacion con categorias es opcional y usa la tabla `categories` existente con scope "JobListing".
- Los comandos programados se registran en el scheduler de Laravel existente.
- Las colas de trabajo usan la configuracion de queues ya establecida en el proyecto (sin Horizon).
- Los emails usan clases Mailable dedicadas siguiendo el patron existente.
