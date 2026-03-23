# Spec 007: Busqueda Publica y Filtrado de Ofertas de Empleo

## Contexto

La Bolsa de Trabajo de Lazos de Fe debe ser accesible para cualquier persona, incluso sin registrarse. Los visitantes anonimos y los miembros registrados necesitan poder explorar las ofertas de empleo disponibles, buscar por palabras clave, filtrar por diferentes criterios y acceder al detalle completo de cada oferta. Esta es la cara publica de la Bolsa de Trabajo y debe ofrecer una experiencia de busqueda intuitiva y eficiente.

## Que debe hacer

### Listado publico de ofertas
- Cualquier persona puede acceder al listado de ofertas de empleo activas sin necesidad de registrarse ni iniciar sesion.
- El listado muestra las ofertas activas (aprobadas y no vencidas) ordenadas por fecha de publicacion, de la mas reciente a la mas antigua.
- Cada oferta en el listado muestra: titulo del puesto, nombre de la organizacion, ciudad, modalidad de trabajo, tipo de contrato, y fecha de publicacion.

### Busqueda por palabras clave
- Los visitantes pueden buscar ofertas por palabras clave que se comparan contra el titulo y la descripcion de la oferta.

### Filtros
- Filtro por categoria de empleo (seleccion de la lista de categorias activas).
- Filtro por modalidad de trabajo (presencial, remoto, hibrido).
- Filtro por tipo de contrato (tiempo completo, medio tiempo, temporal, voluntariado).
- Filtro por ciudad.
- Los filtros son combinables entre si y con la busqueda por palabras clave.

### Ordenamiento
- Ordenar por mas reciente (por defecto).
- Ordenar por fecha limite de postulacion mas proxima.

### Vista de detalle
- Al hacer clic en una oferta, el visitante ve la informacion completa: titulo, descripcion detallada, requisitos, categoria, tipo de contrato, modalidad, ubicacion, rango salarial (si fue proporcionado), fecha de publicacion, fecha limite de postulacion, y la informacion publica de la organizacion (nombre, descripcion, logo, sitio web).
- Si el visitante es un miembro autenticado con perfil de candidato, puede ver un boton para postularse directamente (conectando con el modulo de postulaciones).

### URLs compartibles
- Cada oferta tiene una URL amigable basada en su slug, que puede ser compartida por enlace en redes sociales o mensajeria.

### Paginacion
- Los resultados se paginan para mantener tiempos de carga rapidos.

## Por que es necesario

La busqueda publica es el punto de entrada principal para los candidatos a la plataforma. Si las ofertas no son facilmente descubribles y filtrables, los candidatos no encontraran oportunidades relevantes y las organizaciones no recibiran postulaciones. Una experiencia de busqueda efectiva es critica para el exito de cualquier bolsa de trabajo.

## Dependencias

- **Spec 005** (Ofertas de Empleo): las ofertas deben existir para poder buscarlas y filtrarlas.

## Decisiones de integracion

- La busqueda se implementa usando las capacidades nativas del framework y la base de datos (busqueda por LIKE), sin agregar motores de busqueda externos. Si en el futuro se necesita mejor rendimiento, se puede agregar un motor de busqueda como optimizacion.
- El listado publico se muestra en el panel publico existente (`/app`), el mismo que ya muestra los emprendimientos.
- La vista permite acceso sin autenticacion, pero detecta si el visitante es un miembro autenticado para mostrar opciones adicionales (boton de postulacion).
