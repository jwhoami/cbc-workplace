# Sub-prompt Fase 5 — Guía del Usuario

> **Cómo usar:** copie todo el bloque siguiente en una nueva sesión de
> Claude Code, dentro del repo `cbc-workplace` en una rama nueva
> (sugerido: `docs/guides-fase-5-usuario`). Auto-contenido.

---

## Misión

Escribir la **Guía del Usuario** completa (30–50 páginas) para los dos
tipos de usuarios finales:

1. **Organizaciones** que publican empleos y reciben postulaciones en
   `/member`.
2. **Candidatos** que buscan empleos en el portal público y suscriben
   alertas en `/member`.

Los 10 capítulos viven bajo `docs/guides/user/` y se ensamblan con
`make guides GUIDE=user`.

## Audiencia

Usuarios finales sin conocimiento técnico previo. Hispanohablantes
latinoamericanos. Lectura mayormente en pantalla (PDF, eventualmente
impreso para sesiones de capacitación).

Pronombre: **tú** (cercano, alentador).

## Contexto previo (no necesita lectura adicional)

- `docs/guides/00-blueprint.md` — visión + glosario canónico
- `docs/guides/00-style-guide.md` — convenciones editoriales
- `docs/guides/00-screenshot-inventory.md` — capturas en
  `docs/guides/screenshots/user/`
- `docs/guides/user/metadata.yaml`
- Memorias: `project_bolsa_de_trabajo.md`, `project_cbc_acronym.md`

CBC = **Crossroads Bible Church**. Atribución: solo Juan Carlos Hooper.

## Estructura de capítulos

Crear estos archivos bajo `docs/guides/user/`:

| Archivo | Capítulo | Para quién | Capturas |
|---|---|---|---|
| `01-bienvenida.md` | Qué es CBC Workplace, qué encontrarás | Todos | `user-public-home` |
| `02-buscando-empleos.md` | Portal público, búsqueda, filtros, paginación | Candidato | `user-public-*` |
| `03-registro-y-cuenta.md` | Crear cuenta, verificar email, primer acceso | Todos | `user-register-*`, `user-verify-email-mailpit` |
| `04-perfil-de-organizacion.md` | Crear, editar, esperar verificación | Organización | `user-org-*` |
| `05-publicar-y-gestionar-empleos.md` | Wizard de creación, ciclo de vida, cerrar | Organización | `user-job-*` |
| `06-recibir-y-evaluar-postulaciones.md` | Lista de postulaciones, cambiar estados, notas | Organización | `user-application-*` |
| `07-perfil-de-candidato-y-postular.md` | Completar perfil, experiencia, educación, postular | Candidato | `user-candidate-*` |
| `08-alertas-de-empleo.md` | Crear alerta, frecuencia, recibir digest, unsubscribe | Candidato | `user-alert-*`, `user-mailpit-digest-daily`, `user-unsubscribe-landing` |
| `09-cuando-mi-organizacion-esta-suspendida.md` | Qué significa, qué puedes/no puedes hacer | Organización | `user-org-suspension-banner` |
| `10-preguntas-frecuentes.md` | FAQ con 20–30 preguntas | Todos | (sin capturas) |
| `apendice-a-glosario.md` | Glosario en lenguaje simple | — |

## Reglas de contenido

### Tono y estilo

- Pronombre **tú** consistente.
- Frases cortas (máximo 25 palabras por oración promedio).
- Sin jerga técnica. Si aparece un término técnico, definirlo en la
  primera mención inline:

  > "Una **alerta de empleo** (una notificación automática por correo
  > cuando aparece un empleo que coincide con tus intereses) te ayuda a
  > no perder oportunidades."

- Cada procedimiento abre con "**Para X**:" en negrita.
- Cada capítulo cierra con un párrafo "**¿Algo no funciona?**" remitiendo
  al capítulo 10 (FAQ) o al admin de la iglesia.

### Verificación obligatoria (pero menos densa que Impl)

Las afirmaciones funcionales deben verificarse contra el código pero NO
es necesario citar file:line en el cuerpo (eso satura al lector no
técnico). En cambio, mantener un **archivo oculto** `docs/guides/user/
_verification.md` (no se incluye en el `.docx`) donde cada afirmación va
con su file:line para que el reviewer técnico audite.

Ejemplos a verificar:

- "La verificación de tu organización puede tomar hasta 72 horas." →
  ¿hay un SLA real? Si no, no afirmar el plazo; en su lugar: "será
  revisada por el equipo administrador".
- "Recibirás un correo cuando tu perfil sea aprobado." → verificar en
  `app/Mail/Member/AffiliateRequestApproved.php`
- "Las alertas instantáneas llegan en menos de 5 minutos." → verificar
  `config/alerts.php` (`INSTANT_ALERT_WINDOW_SECONDS=300`)

### Capturas

Mostrar capturas grandes, una por procedimiento clave. Las anotaciones
guían el ojo. Las capturas con muchos números (5+) deben acompañarse de
una lista de leyendas en lugar de saturar la imagen.

### Callouts

- **Nota.** Detalles secundarios.
- **Atención.** Cosas que pueden confundirte si no las sabes.
- **Importante.** Acciones que no se pueden deshacer (borrar postulación,
  cerrar oferta).
- **Buena práctica.** Recomendaciones de uso eficiente.
- **Solo administradores.** Cuando menciones funcionalidad que el usuario
  no verá.

### Glosario

Glosario en lenguaje simple en `apendice-a-glosario.md`. Términos como
"Filament", "Action", "Policy" NO van aquí (son técnicos).

## Entregables

11 archivos `.md` totalizando 30–50 páginas. Promedio 3–5 páginas por
capítulo; capítulo 10 (FAQ) puede ser más largo.

## Definición de "hecho"

```bash
make guides GUIDE=user
# produce: docs/guides/build/cbc-workplace-user.docx

# Validar:
#   - Capturas grandes y legibles
#   - Sin jerga técnica
#   - Pronombre "tú" consistente
#   - 20+ preguntas en FAQ
#   - No hay file:line en el cuerpo
#   - _verification.md auditable existe
```

Test de legibilidad: leer el capítulo 2 en voz alta. Si suena natural,
está bien. Si suena como manual técnico, reescribir.

## Cierre

- Commit:
  `feat(docs): guía del usuario completa (10 capítulos + apéndice)`
- Push a `hooperits/cbc-workplace`.
- PR contra `main` o rama de coordinación.
