# Spec 005: Publicacion y Gestion de Ofertas de Empleo

## Contexto

Este es el modulo central de la Bolsa de Trabajo de Lazos de Fe. Permite a las organizaciones verificadas crear, editar y administrar sus ofertas de empleo (vacantes). Las ofertas pasan por un flujo de aprobacion administrado antes de ser visibles publicamente, siguiendo el mismo patron que ya existe para los emprendimientos en la plataforma. Este flujo protege la calidad del contenido y asegura que las ofertas sean legitimas y apropiadas para la comunidad.

## Que debe hacer

### Creacion y edicion de ofertas
- Un miembro que administra una organizacion verificada puede crear ofertas de empleo con los siguientes datos: titulo del puesto, descripcion detallada del puesto, requisitos del puesto, categoria de empleo (seleccionada de las categorias creadas en Spec 002), tipo de contrato (tiempo completo, medio tiempo, temporal, voluntariado), modalidad de trabajo (presencial, remoto, hibrido), ciudad y provincia del puesto, rango salarial minimo y maximo con moneda (opcionales, por defecto USD), fecha limite de postulacion, y hasta 5 preguntas adicionales personalizadas para los postulantes.
- Cada oferta genera automaticamente un slug unico para una URL publica amigable.
- El miembro puede editar una oferta mientras este en estado borrador o si fue rechazada.
- El miembro puede previsualizar la oferta antes de enviarla a aprobacion.

### Flujo de aprobacion
- Las ofertas inician en estado "borrador".
- El miembro envia la oferta a aprobacion, cambiando su estado a "pendiente".
- Los administradores reciben notificacion por email cuando una oferta solicita aprobacion.
- Los administradores pueden aprobar o rechazar la oferta, proporcionando un motivo en caso de rechazo.
- Si la oferta es aprobada, se activa y se hace visible publicamente, registrando la fecha de publicacion.
- Si la oferta es rechazada, el miembro recibe notificacion por email con el motivo, puede editar y reenviar.
- Toda accion de aprobacion o rechazo queda registrada en el log de actividad.

### Gestion del ciclo de vida
- El miembro puede cerrar anticipadamente una oferta activa (por ejemplo, si ya encontro candidato).
- Las ofertas vencen automaticamente al pasar su fecha limite de postulacion.
- Se registra un contador de visualizaciones de cada oferta.
- Los administradores pueden gestionar todas las ofertas desde el panel admin: listar, filtrar por estado, ver detalle, aprobar, rechazar.

### Panel administrativo
- Vista de lista de ofertas con filtros por estado de aprobacion, categoria, organizacion.
- Accion de aprobacion/rechazo con modal para motivo.
- Vista de detalle con toda la informacion de la oferta y la organizacion que la publico.

### Panel de miembro
- El miembro ve solo las ofertas de su organizacion.
- Puede crear, editar, enviar a aprobacion y cerrar sus ofertas.

## Por que es necesario

Las ofertas de empleo son el corazon de la Bolsa de Trabajo. Sin este modulo, las organizaciones no tienen forma de publicar vacantes ni los candidatos tienen ofertas a las cuales postularse. El flujo de aprobacion garantiza que solo ofertas apropiadas y verificadas sean visibles para la comunidad, manteniendo la confianza y calidad de la plataforma.

## Dependencias

- **Spec 002** (Categorias de Empleo): las ofertas se clasifican por categoria.
- **Spec 003** (Organizaciones): solo organizaciones verificadas pueden crear ofertas.

## Decisiones de integracion

- Las ofertas usan la relacion polimorfica existente con categorias (via `categorizables` con scope "JobListing").
- El flujo de aprobacion sigue el mismo patron que la aprobacion de emprendimientos: acciones dedicadas, estados como enums enteros, notificaciones por email, log de actividad.
- Se crea un recurso base compartido (similar a `BaseVentureResource`) para reutilizar logica de formulario, tabla e infolist entre los paneles Admin, Member y publico.
- Los permisos de aprobacion se agregan al sistema de roles existente.
