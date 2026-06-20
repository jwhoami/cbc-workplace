# Spec 004: Perfil de Candidato, Experiencia Laboral y Educacion

## Contexto

Los miembros de Lazos de Fe que deseen postularse a ofertas de empleo necesitan construir un perfil profesional dentro de la plataforma. Este perfil complementa su cuenta de miembro existente con informacion relevante para los empleadores: experiencia laboral, educacion, habilidades, hoja de vida (CV) y una declaracion de fe opcional. El perfil de candidato es la carta de presentacion del miembro ante las organizaciones que buscan talento.

## Que debe hacer

### Perfil profesional del candidato
- Un miembro registrado puede crear su perfil de candidato con los siguientes datos: titulo profesional breve (headline), descripcion/resumen profesional, ciudad y provincia de residencia, telefono de contacto, foto de perfil.
- El candidato puede subir su hoja de vida (CV) en formato PDF, con un tamano maximo de 5 MB.
- El candidato puede incluir una declaracion de fe o testimonio de manera opcional, describiendo brevemente su fe o ministerio activo.
- El candidato puede controlar la visibilidad de su perfil: visible u oculto para las organizaciones. Si el perfil esta oculto, las organizaciones no pueden verlo al revisar postulaciones (el candidato aun puede postularse, pero su perfil no aparece en busquedas).

### Experiencia laboral
- El candidato puede agregar multiples experiencias laborales, cada una con: nombre de la empresa u organizacion, cargo desempenado, descripcion de funciones y logros, fecha de inicio, fecha de fin (nula si es el trabajo actual), y un indicador de si es su trabajo actual.
- Las experiencias se ordenan de la mas reciente a la mas antigua.
- El candidato puede editar y eliminar sus experiencias laborales.

### Educacion
- El candidato puede agregar multiples registros de educacion, cada uno con: nombre de la institucion educativa, titulo o certificacion obtenida, campo o area de estudio, ano de graduacion (nulo si esta en curso), y un indicador de si esta en curso.
- El candidato puede editar y eliminar sus registros de educacion.

### Vista administrativa
- Los administradores pueden ver los perfiles de candidato en modo lectura desde el panel administrativo para efectos de moderacion.

## Por que es necesario

Sin un perfil profesional, los candidatos no pueden presentar sus credenciales ante las organizaciones y no podran postularse a ofertas de empleo en specs futuras. El perfil de candidato es el puente entre los miembros de la comunidad y las oportunidades laborales. La declaracion de fe opcional refuerza el proposito de la plataforma: conectar talento que comparte valores cristianos con organizaciones que los valoran.

## Dependencias

- Ninguna directa. Esta spec es parallelizable con Spec 003 (Organizaciones).
- El perfil de candidato sera prerequisito para Spec 006 (Postulaciones).

## Decisiones de integracion

- El perfil de candidato es una extension del miembro existente (`Member`), conectado via una relacion uno a uno. No se crea un nuevo modelo de usuario ni un nuevo guard de autenticacion.
- La experiencia laboral y la educacion son relaciones uno a muchos del perfil de candidato.
- La gestion del perfil se realiza en el panel de miembro existente (`/member`).
- Los archivos (CV, foto) se almacenan usando el disco local de Laravel, no S3.
