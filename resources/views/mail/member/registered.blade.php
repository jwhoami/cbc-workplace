<x-mail::message>
# Estimado(a) {{ $user->name }}

# Bienvenidos a {{ config('app.name') }}

Usted se registro exitosamente.

El perfil de su usuario es de tipo "Visitante". Bajo este perfil, usted podrá navegar en el sitio
y mantener sus favoritos.

Para publicar sus emprendimientos en el portal, usted debe convertirse a un Afiliado. Para afiliarse,
acceda su perfil y haga clic en "Solicitar Afiliación" y siga los pasos.

Una vez aprobado su solicitud, usted podrá publicar su emprendimientos.

  Bendiciones,<br>
{{ config('app.name') }}
</x-mail::message>
