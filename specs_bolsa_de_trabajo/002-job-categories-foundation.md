# Spec 002: Categorias de Empleo para Bolsa de Trabajo

## Contexto

La Bolsa de Trabajo de Lazos de Fe necesita un sistema de categorias de empleo para clasificar las ofertas laborales que publiquen las organizaciones. La plataforma ya cuenta con un sistema de categorias jerarquicas utilizado para clasificar emprendimientos. Las categorias de empleo deben integrarse con ese sistema existente, reutilizando la misma tabla y modelo pero con un scope distinto ("JobListing") para diferenciarlas de las categorias de emprendimientos ("Venture").

## Que debe hacer

- Extender la tabla de categorias existente para soportar campos adicionales que las categorias de empleo necesitan: un slug para URLs amigables y un icono representativo.
- Crear un conjunto inicial de 9 categorias de empleo que representen las areas laborales mas comunes en el contexto de la plataforma: Administracion y Finanzas, Tecnologia e Informatica, Educacion y Docencia, Pastoral y Ministerio, Comunicacion y Medios, Salud y Bienestar, Servicios Generales, Diseno y Creatividad, y Voluntariado.
- Permitir a los administradores gestionar las categorias de empleo desde el panel administrativo, pudiendo crear, editar y organizar estas categorias de forma independiente a las categorias de emprendimientos.
- Los campos de slug e icono solo deben ser visibles y editables cuando la categoria corresponda al ambito de empleo.

## Por que es necesario

Las categorias de empleo son un prerequisito fundamental para todo el modulo de Bolsa de Trabajo. Sin ellas, las organizaciones no podran clasificar sus ofertas laborales y los candidatos no podran filtrar empleos por area de interes. Al reutilizar el sistema existente de categorias se evita duplicar logica y se mantiene la consistencia arquitectonica del proyecto.

## Dependencias

- Ninguna. Esta es la primera spec del modulo de Bolsa de Trabajo y no depende de otras specs.

## Decisiones de integracion

- Las categorias de empleo reutilizan la tabla `categories` existente con `scope="JobListing"`, no se crea una tabla nueva.
- El modelo `Category` existente se reutiliza tal cual, los nuevos campos (slug, icon) se agregan via migracion.
- La relacion polimorfica con `categorizables` se mantiene para conectar categorias con ofertas de empleo en specs futuras.
