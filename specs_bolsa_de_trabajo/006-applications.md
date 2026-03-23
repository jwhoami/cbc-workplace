# Spec 006: Sistema de Postulaciones

## Contexto

El sistema de postulaciones es el nucleo transaccional de la Bolsa de Trabajo de Lazos de Fe. Conecta a los candidatos (miembros con perfil profesional) con las ofertas de empleo publicadas por las organizaciones. Permite a los candidatos postularse a vacantes y a las organizaciones gestionar y evaluar a los postulantes a traves de un flujo de estados que refleja el proceso de seleccion.

## Que debe hacer

### Postulacion del candidato
- Un miembro que tiene un perfil de candidato puede postularse a una oferta de empleo activa.
- Al postularse, el candidato adjunta su CV (se toma una copia del CV actual del perfil como snapshot, para que cambios futuros al CV no afecten postulaciones previas).
- El candidato puede incluir una carta de presentacion opcional.
- Si la oferta tiene preguntas adicionales configuradas por la organizacion, el candidato debe responderlas al momento de postularse.
- Un candidato solo puede postularse una vez por oferta. Si ya se postulo, la opcion no esta disponible.
- Solo se puede postular a ofertas que esten activas (no cerradas, no vencidas).
- El candidato recibe un email de confirmacion al enviar su postulacion.
- La organizacion recibe un email de notificacion cuando recibe una nueva postulacion.

### Gestion de postulaciones por la organizacion
- El miembro que administra la organizacion puede ver la lista de postulantes de cada oferta.
- Para cada postulante puede ver: CV, perfil de candidato (si es visible), carta de presentacion, respuestas a las preguntas adicionales, y la fecha de postulacion.
- La organizacion puede cambiar el estado de cada postulacion a traves del flujo: recibida, en revision, entrevista, rechazada, aceptada.
- La organizacion puede agregar notas internas sobre cada postulante (no visibles para el candidato).
- El candidato recibe un email de notificacion cada vez que cambia el estado de su postulacion.

### Vista del candidato
- El candidato puede ver el historial de todas sus postulaciones con el estado actual de cada una.
- La postulacion es de solo lectura despues de enviada (no se puede editar ni retirar).

### Vista administrativa
- Los administradores pueden ver todas las postulaciones en el sistema en modo lectura, para efectos de moderacion y soporte.

## Por que es necesario

Las postulaciones son el punto de encuentro entre candidatos y organizaciones. Sin este modulo, la plataforma seria solo un tablero de anuncios sin interaccion real. El sistema de estados permite a las organizaciones gestionar su proceso de seleccion de manera organizada, y las notificaciones por email mantienen a los candidatos informados sobre el progreso de sus postulaciones, generando confianza y transparencia.

## Dependencias

- **Spec 004** (Perfil de Candidato): un candidato debe tener perfil profesional para postularse.
- **Spec 005** (Ofertas de Empleo): las postulaciones se hacen sobre ofertas activas.

## Decisiones de integracion

- Las postulaciones pertenecen a un miembro (`Member`), no a un usuario separado.
- Los estados de postulacion siguen el patron de enums enteros con etiquetas traducibles.
- Las notificaciones por email usan clases Mailable dedicadas, siguiendo el patron existente.
- La gestion de postulantes se integra como un relation manager dentro del recurso de ofertas de empleo en el panel de miembro.
- El log de actividad registra cada cambio de estado.
