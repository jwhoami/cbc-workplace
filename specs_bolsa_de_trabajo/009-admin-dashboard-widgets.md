# Spec 009: Dashboard Administrativo y Widgets de Bolsa de Trabajo

## Contexto

Con todos los modulos funcionales de la Bolsa de Trabajo implementados (organizaciones, ofertas, postulaciones, busqueda, alertas), los administradores necesitan una vista consolidada del estado de la plataforma. Este modulo agrega widgets al dashboard del panel administrativo que proporcionan estadisticas clave y acceso rapido a elementos que requieren atencion, ademas de organizar la navegacion del panel admin para incluir una seccion dedicada a la Bolsa de Trabajo.

## Que debe hacer

### Widgets de dashboard
- Widget de estadisticas generales de la Bolsa de Trabajo: total de candidatos con perfil, total de organizaciones registradas (y cuantas estan verificadas), total de ofertas activas, y total de postulaciones recibidas en las ultimas 24 horas.
- Widget de postulaciones recientes: las ultimas 10 postulaciones recibidas con nombre del candidato, titulo de la oferta y fecha.
- Widget de verificaciones pendientes: organizaciones que estan esperando ser verificadas, con enlace directo para revisar cada una.
- Widget de ofertas pendientes de aprobacion: ofertas enviadas a aprobacion que esperan decision del administrador, con enlace directo para revisar cada una.

### Navegacion del panel admin
- Agregar un grupo de navegacion "Bolsa de Trabajo" en el panel administrativo que agrupe todos los recursos relacionados: Organizaciones, Ofertas de Empleo, Postulaciones, Categorias de Empleo.
- Este grupo se muestra junto a los grupos existentes (Sistema, Administracion, Emprendimientos).

### Suspension de organizacion
- Los administradores pueden suspender una organizacion, lo cual automaticamente desactiva todas sus ofertas de empleo activas.
- La organizacion y el miembro que la administra reciben notificacion de la suspension.
- Toda la accion queda registrada en el log de actividad.

## Por que es necesario

Sin un dashboard consolidado, los administradores tendrian que navegar por multiples secciones para entender el estado de la Bolsa de Trabajo y atender solicitudes pendientes. Los widgets proporcionan visibilidad inmediata de lo que requiere atencion (verificaciones y aprobaciones pendientes) y metricas generales de la actividad de la plataforma. La navegacion organizada facilita el acceso a todas las herramientas de gestion de la Bolsa de Trabajo.

## Dependencias

- **Spec 003** (Organizaciones): para el widget de verificaciones pendientes y la accion de suspension.
- **Spec 005** (Ofertas de Empleo): para el widget de ofertas pendientes y estadisticas.
- **Spec 006** (Postulaciones): para el widget de postulaciones recientes y estadisticas.

## Decisiones de integracion

- Los widgets se agregan al dashboard existente del panel admin, no se crea un dashboard separado.
- La navegacion usa el sistema de grupos de navegacion que ya existe en el AdminPanelProvider.
- La accion de suspension de organizacion sigue el patron de acciones existente con log de actividad y notificaciones por email.
- No se crean modelos nuevos; los widgets consultan los modelos existentes.
