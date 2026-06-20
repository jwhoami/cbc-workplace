# Spec 003: Perfil de Organizacion y Verificacion

## Contexto

La Bolsa de Trabajo de Lazos de Fe conecta organizaciones que buscan talento con candidatos que comparten valores cristianos. Las organizaciones pueden ser iglesias, ministerios, organizaciones sin fines de lucro, empresas privadas o emprendimientos. Antes de poder publicar ofertas de empleo, una organizacion debe registrar su perfil y pasar por un proceso de verificacion administrado por el equipo de la plataforma. Este flujo de verificacion es similar al flujo de aprobacion de emprendimientos y membresias que ya existe en la plataforma.

## Que debe hacer

### Perfil de organizacion
- Un miembro registrado puede crear el perfil de su organizacion con los siguientes datos: nombre legal, nombre visible en la plataforma, tipo de entidad (iglesia, ministerio, organizacion sin fines de lucro, empresa privada, emprendimiento), denominacion eclesiastica (opcional, si aplica), descripcion de la organizacion, declaracion de cultura organizacional y valores que buscan en sus colaboradores, logo, sitio web, email de contacto, telefono, ciudad, provincia y pais (por defecto Panama).
- Un miembro solo puede administrar una organizacion.
- El miembro puede editar los datos de su organizacion en cualquier momento.

### Flujo de verificacion
- Al crear su perfil, la organizacion queda en estado "pendiente de verificacion".
- El miembro puede solicitar la verificacion de su organizacion.
- Los administradores reciben una notificacion por email cuando una organizacion solicita verificacion.
- Los administradores pueden aprobar (verificar) o suspender una organizacion, proporcionando un motivo en caso de suspension.
- La organizacion recibe notificacion por email cuando es verificada o suspendida.
- Solo las organizaciones verificadas podran publicar ofertas de empleo (esto se validara en specs futuras).
- Toda accion de verificacion o suspension debe quedar registrada en el log de actividad.

### Panel administrativo
- Los administradores pueden ver la lista de todas las organizaciones, filtrar por estado de verificacion (pendiente, verificada, suspendida), y acceder al detalle de cada una.
- Los administradores pueden ejecutar la accion de verificar o suspender directamente desde la vista de detalle.

## Por que es necesario

Las organizaciones son la entidad central que publica ofertas de empleo. Sin un perfil de organizacion verificado, no hay garantia de la legitimidad de las ofertas publicadas. El proceso de verificacion protege a los candidatos y asegura la calidad del contenido en la plataforma. Esto es especialmente importante en una comunidad basada en la fe donde la confianza es un valor fundamental.

## Dependencias

- Ninguna directa, aunque idealmente se implementa despues de Spec 002 (categorias de empleo) para una navegacion coherente en el panel admin.

## Decisiones de integracion

- La organizacion pertenece a un miembro existente (`Member`), no se crea un nuevo modelo de autenticacion.
- El flujo de verificacion sigue el mismo patron que la aprobacion de emprendimientos y membresias existente (acciones dedicadas, notificaciones por email, log de actividad, comentarios polimorficos).
- Se reutilizan los tres paneles existentes: Admin para gestion/verificacion, Member para que el miembro cree y edite su organizacion.
- Los permisos se agregan al sistema de roles existente (array `perm` en el modelo `Role`).
